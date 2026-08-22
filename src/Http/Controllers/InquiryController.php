<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\InquiryRequest;
use ME\MerchandisingTrace\Models\Buyer;
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
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('inquiry_no', 'like', '%' . $request->search . '%')->orWhere('description', 'like', '%' . $request->search . '%')))
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

        $inquiry = DB::transaction(function () use ($data, $numbers) {
            $inquiry = Inquiry::create([
                'inquiry_no' => $numbers->next(Inquiry::class, 'inquiry_no', config('merchandising-trace.document_prefixes.inquiry')),
                'inquiry_given_date' => $data['inquiry_given_date'],
                'buyer_id' => $data['buyer_id'],
                'season_id' => $data['season_id'] ?? null,
                'merchandiser_id' => $data['merchandiser_id'] ?? null,
                'factory_id' => $data['factory_id'] ?? null,
                'order_confirmation_due_date' => $data['order_confirmation_due_date'] ?? null,
                'product_type_id' => $data['product_type_id'] ?? null,
                'description' => $data['description'] ?? null,
                'target_qty' => $data['target_qty'] ?? null,
                'target_price' => $data['target_price'] ?? null,
                'target_ship_date' => $data['target_ship_date'] ?? null,
                'status' => $data['status'],
                'lost_reason' => $data['lost_reason'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] ?? [] as $line) {
                if (empty(array_filter($line))) {
                    continue;
                }
                $inquiry->items()->create($line);
            }

            return $inquiry;
        });

        return redirect()->route('merchandising-trace.inquiries.show', $inquiry)->with('success', "Inquiry {$inquiry->inquiry_no} created successfully.");
    }

    public function show(Inquiry $inquiry): View
    {
        $this->authorize('merch_inquiry.view');

        $inquiry->load(['buyer', 'season', 'merchandiser', 'factory', 'productType', 'items.productType', 'styles']);

        return view('merchandising-trace::admin.inquiries.show', ['inquiry' => $inquiry]);
    }

    public function edit(Inquiry $inquiry): View
    {
        $this->authorize('merch_inquiry.edit');

        $inquiry->load('items');

        return view('merchandising-trace::admin.inquiries.edit', ['inquiry' => $inquiry] + $this->formOptions());
    }

    public function update(InquiryRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $inquiry) {
            $inquiry->update([
                'inquiry_given_date' => $data['inquiry_given_date'],
                'buyer_id' => $data['buyer_id'],
                'season_id' => $data['season_id'] ?? null,
                'merchandiser_id' => $data['merchandiser_id'] ?? null,
                'factory_id' => $data['factory_id'] ?? null,
                'order_confirmation_due_date' => $data['order_confirmation_due_date'] ?? null,
                'product_type_id' => $data['product_type_id'] ?? null,
                'description' => $data['description'] ?? null,
                'target_qty' => $data['target_qty'] ?? null,
                'target_price' => $data['target_price'] ?? null,
                'target_ship_date' => $data['target_ship_date'] ?? null,
                'status' => $data['status'],
                'lost_reason' => $data['status'] === 'lost' ? ($data['lost_reason'] ?? null) : null,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $inquiry->items()->delete();
            foreach ($data['items'] ?? [] as $line) {
                if (empty(array_filter($line))) {
                    continue;
                }
                $inquiry->items()->create($line);
            }
        });

        return redirect()->route('merchandising-trace.inquiries.show', $inquiry)->with('success', 'Inquiry updated successfully.');
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $this->authorize('merch_inquiry.delete');

        $inquiry->delete();

        return redirect()->route('merchandising-trace.inquiries.index')->with('success', 'Inquiry deleted successfully.');
    }

    /**
     * §M02 AC: converting an inquiry auto-creates the Style (development
     * status = new) and carries buyer/season/merchandiser/factory/product
     * type forward — no re-typing.
     */
    public function convertToStyle(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $this->authorize('merch_inquiry.edit');

        $request->validate([
            'style_no' => ['required', 'string', 'max:150', 'unique:mer_styles,style_no'],
            'name' => ['required', 'string', 'max:150'],
        ]);

        $style = DB::transaction(function () use ($request, $inquiry) {
            $style = Style::create([
                'style_no' => $request->style_no,
                'name' => $request->name,
                'buyer_id' => $inquiry->buyer_id,
                'inquiry_id' => $inquiry->id,
                'season_id' => $inquiry->season_id,
                'merchandiser_id' => $inquiry->merchandiser_id,
                'product_type_id' => $inquiry->product_type_id,
                'development_status' => 'new',
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            if ($inquiry->status === 'open') {
                $inquiry->update(['status' => 'quoted']);
            }

            return $style;
        });

        return redirect()->route('merchandising-trace.styles.index')->with('success', "Style {$style->style_no} created from inquiry {$inquiry->inquiry_no}.");
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
