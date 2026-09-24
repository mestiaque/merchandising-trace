<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\InquiryRequest;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\Factory;
use ME\MerchandisingTrace\Models\Inquiry;
use ME\MerchandisingTrace\Models\ProductType;
use ME\MerchandisingTrace\Models\Season;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_inquiry.list');

        $inquiries = Inquiry::query()
            ->with(['buyer', 'merchandiser', 'productType'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
                ->where('inquiry_no', 'like', '%' . $request->search . '%')
                ->orWhere('style_ref', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.inquiries.index', ['inquiries' => $inquiries]);
    }

    public function create(): View
    {
        $this->authorize('merch_inquiry.add');

        return view('merchandising-trace::admin.inquiries.create', $this->formOptions());
    }

    public function store(InquiryRequest $request, DocumentNumberService $numbers): RedirectResponse
    {
        $data = $request->validated();

        $inquiry = Inquiry::create($data + [
            'inquiry_no' => $numbers->next(Inquiry::class, 'inquiry_no', config('merchandising-trace.document_prefixes.inquiry')),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('merchandising-trace.inquiries.show', $inquiry)->with('success', "Inquiry {$inquiry->inquiry_no} created successfully.");
    }

    public function show(Inquiry $inquiry): View
    {
        $this->authorize('merch_inquiry.view');

        $inquiry->load(['buyer', 'season', 'merchandiser', 'factory', 'productType', 'items.productType', 'techPack', 'costSheets']);

        return view('merchandising-trace::admin.inquiries.show', ['inquiry' => $inquiry]);
    }

    public function edit(Inquiry $inquiry): View
    {
        $this->authorize('merch_inquiry.edit');

        return view('merchandising-trace::admin.inquiries.edit', ['inquiry' => $inquiry] + $this->formOptions());
    }

    public function update(InquiryRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validated();
        $data['lost_reason'] = $data['status'] === 'lost' ? ($data['lost_reason'] ?? null) : null;

        $inquiry->update($data);

        return redirect()->route('merchandising-trace.inquiries.show', $inquiry)->with('success', 'Inquiry updated successfully.');
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $this->authorize('merch_inquiry.delete');

        $inquiry->delete();

        return redirect()->route('merchandising-trace.inquiries.index')->with('success', 'Inquiry deleted successfully.');
    }

    /**
     * §M02 AC: an inquiry becomes its Tech Pack (Style) with everything the
     * inquiry already knows carried forward — no re-typing. One inquiry is
     * one item, so it gets exactly one tech pack; cost sheets costed against
     * the inquiry before the style existed are linked to it here.
     */
    public function convertToStyle(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $this->authorize('merch_inquiry.edit');

        if ($existing = $inquiry->techPack) {
            return redirect()->route('merchandising-trace.styles.show', $existing)
                ->with('error', "Inquiry {$inquiry->inquiry_no} already has tech pack {$existing->style_no}.");
        }

        $request->validate([
            'style_no' => ['required', 'string', 'max:150', 'unique:mer_styles,style_no'],
            'name' => ['required', 'string', 'max:150'],
        ]);

        $style = DB::transaction(function () use ($request, $inquiry) {
            $style = Style::create([
                'style_no' => $request->style_no,
                'name' => $request->name,
                'description' => $inquiry->description,
                'buyer_id' => $inquiry->buyer_id,
                'inquiry_id' => $inquiry->id,
                'season_id' => $inquiry->season_id,
                'merchandiser_id' => $inquiry->merchandiser_id,
                'product_type_id' => $inquiry->product_type_id,
                'development_status' => 'new',
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            CostSheet::query()->where('inquiry_id', $inquiry->id)->whereNull('style_id')->update(['style_id' => $style->id]);

            if ($inquiry->status === 'open') {
                $inquiry->update(['status' => 'quoted']);
            }

            return $style;
        });

        return redirect()->route('merchandising-trace.styles.index')->with('success', "Tech pack {$style->style_no} created from inquiry {$inquiry->inquiry_no}.");
    }

    private function formOptions(): array
    {
        return [
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
            'seasonsOptions' => Season::query()->active()->orderBy('name')->get(),
            'merchandisersOptions' => User::query()->orderBy('name')->get(),
            'factoriesOptions' => Factory::query()->active()->orderBy('name')->get(),
            'productTypesOptions' => ProductType::query()->active()->orderBy('name')->get(),
        ];
    }
}
