<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\CostSheetRequest;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\Currency;
use ME\MerchandisingTrace\Models\Inquiry;
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Models\Uom;
use ME\MerchandisingTrace\Services\DocumentNumberService;
use ME\MerchandisingTrace\Support\RichText;

class CostSheetController extends Controller
{
    private const IMAGE_FIELDS = ['front_image', 'back_image', 'sketch_image'];

    public function index(Request $request): View
    {
        $this->authorize('merch_costing.list');

        $costSheets = CostSheet::query()
            ->with(['style', 'buyer', 'inquiry'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
                ->where('cost_sheet_no', 'like', '%' . $request->search . '%')
                ->orWhere('style_ref', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.cost-sheets.index', ['costSheets' => $costSheets]);
    }

    /**
     * ?inquiry_id= / ?style_id= open the sheet pre-filled from that record,
     * so nothing it already knows is typed again.
     */
    public function create(Request $request): View
    {
        $this->authorize('merch_costing.add');

        return view('merchandising-trace::admin.cost-sheets.create', [
            'prefill' => $this->prefill($request),
        ] + $this->formOptions());
    }

    public function store(CostSheetRequest $request, DocumentNumberService $numbers): RedirectResponse
    {
        $data = $request->validated();

        $costSheet = DB::transaction(function () use ($data, $request, $numbers) {
            $costSheet = new CostSheet($this->headerData($data) + [
                'cost_sheet_no' => $numbers->next(CostSheet::class, 'cost_sheet_no', config('merchandising-trace.document_prefixes.costing', 'CST')),
                'version' => $this->nextVersion($data['style_id'] ?? null, $data['inquiry_id'] ?? null),
                'status' => 'draft',
                'prepared_by' => auth()->id(),
            ]);
            $this->applyImages($costSheet, $request);
            $costSheet->save();

            $this->saveItems($costSheet, $data['items'] ?? []);
            $costSheet->recompute();

            return $costSheet;
        });

        return redirect()->route('merchandising-trace.cost-sheets.show', $costSheet)->with('success', "Cost Sheet {$costSheet->cost_sheet_no} created successfully.");
    }

    public function show(CostSheet $costSheet): View
    {
        $this->authorize('merch_costing.view');

        $costSheet->load(['style.images', 'inquiry', 'buyer', 'currency', 'items.item', 'items.uom', 'preparer', 'approver']);

        return view('merchandising-trace::admin.cost-sheets.show', compact('costSheet'));
    }

    public function edit(CostSheet $costSheet): View
    {
        $this->authorize('merch_costing.edit');

        $costSheet->load(['items', 'style.images']);

        return view('merchandising-trace::admin.cost-sheets.edit', ['costSheet' => $costSheet, 'prefill' => []] + $this->formOptions());
    }

    public function update(CostSheetRequest $request, CostSheet $costSheet): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $costSheet) {
            $header = $this->headerData($data);
            // Moving the sheet to another style/inquiry starts a new version there.
            if ($costSheet->style_id != ($header['style_id'] ?? null) || $costSheet->inquiry_id != ($header['inquiry_id'] ?? null)) {
                $header['version'] = $this->nextVersion($header['style_id'] ?? null, $header['inquiry_id'] ?? null, $costSheet->id);
            }

            $costSheet->fill($header);
            $this->applyImages($costSheet, $request, $data['remove_images'] ?? []);
            $costSheet->save();

            $costSheet->items()->delete();
            $this->saveItems($costSheet, $data['items'] ?? []);
            $costSheet->recompute();
        });

        return redirect()->route('merchandising-trace.cost-sheets.show', $costSheet)->with('success', 'Cost Sheet updated successfully.');
    }

    public function destroy(CostSheet $costSheet): RedirectResponse
    {
        $this->authorize('merch_costing.delete');

        $costSheet->delete();

        return redirect()->route('merchandising-trace.cost-sheets.index')->with('success', 'Cost Sheet deleted successfully.');
    }

    /**
     * §M06 AC: "submit -> approve workflow, PDF export in buyer format" —
     * the PDF is the Open Cost Sheet layout itself.
     */
    public function pdf(CostSheet $costSheet)
    {
        $this->authorize('merch_costing.view');

        $costSheet->load(['style.images', 'buyer', 'currency', 'items.item', 'items.uom']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('merchandising-trace::admin.cost-sheets.pdf', compact('costSheet'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("{$costSheet->cost_sheet_no}.pdf");
    }

    /** Standalone, letterhead-only page that opens the browser's print dialog. */
    public function print(CostSheet $costSheet): View
    {
        $this->authorize('merch_costing.view');

        $costSheet->load(['style.images', 'buyer', 'currency', 'items.item', 'items.uom']);

        return view('merchandising-trace::admin.cost-sheets.print', compact('costSheet'));
    }

    /**
     * §M06: only an approved cost sheet may be linked to a sales contract.
     */
    public function approve(CostSheet $costSheet): RedirectResponse
    {
        $this->authorize('merch_costing.edit');

        $costSheet->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', "Cost Sheet {$costSheet->cost_sheet_no} approved.");
    }

    private function headerData(array $data): array
    {
        return [
            'style_id' => $data['style_id'] ?? null,
            'inquiry_id' => $data['inquiry_id'] ?? null,
            'style_ref' => $data['style_ref'] ?? null,
            'buyer_id' => $data['buyer_id'],
            'garment_description' => $data['garment_description'] ?? null,
            'size_range' => $data['size_range'] ?? null,
            'costing_date' => $data['costing_date'] ?? now()->toDateString(),
            'currency_id' => $data['currency_id'] ?? null,
            'order_qty' => $data['order_qty'] ?? null,
            'smv' => $data['smv'] ?? null,
            'cm_minute_rate' => $data['cm_minute_rate'] ?? null,
            'efficiency_percent' => $data['efficiency_percent'] ?? 100,
            // CM is entered per dozen (as printed); 0 lets recompute() derive it from SMV.
            'cm_cost' => isset($data['cm_per_dozen']) ? (float) $data['cm_per_dozen'] / 12 : 0,
            'commercial_percent' => $data['commercial_percent'] ?? 0,
            'buyer_target_price' => $data['buyer_target_price'] ?? null,
            'final_price' => $data['final_price'] ?? null,
            'price_type' => $data['price_type'],
            'remarks' => $data['remarks'] ?? null,
        ];
    }

    private function saveItems(CostSheet $costSheet, array $items): void
    {
        foreach ($items as $line) {
            if (empty($line['description']) && empty($line['item_id'])) {
                continue;
            }
            $costSheet->items()->create($line);
        }
    }

    private function applyImages(CostSheet $costSheet, Request $request, array $remove = []): void
    {
        foreach (self::IMAGE_FIELDS as $field) {
            $replace = $request->hasFile($field);
            if (($replace || in_array($field, $remove, true)) && $costSheet->{$field}) {
                Storage::disk('public')->delete($costSheet->{$field});
                $costSheet->{$field} = null;
            }
            if ($replace) {
                $costSheet->{$field} = $request->file($field)->store('merchandising-trace/cost-sheet-images', 'public');
            }
        }
    }

    /** Versions count per style; before a style exists, per inquiry. */
    private function nextVersion(?int $styleId, ?int $inquiryId, ?int $exceptId = null): int
    {
        $query = CostSheet::withTrashed()->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId));

        if ($styleId) {
            $query->where('style_id', $styleId);
        } elseif ($inquiryId) {
            $query->whereNull('style_id')->where('inquiry_id', $inquiryId);
        } else {
            return 1;
        }

        return ((int) $query->max('version')) + 1;
    }

    private function prefill(Request $request): array
    {
        $prefill = [];

        if ($request->filled('inquiry_id') && ($inquiry = Inquiry::with(['productType', 'techPack'])->find((int) $request->input('inquiry_id')))) {
            $prefill = [
                'inquiry_id' => $inquiry->id,
                'style_id' => $inquiry->techPack?->id,
                'buyer_id' => $inquiry->buyer_id,
                'style_ref' => $inquiry->techPack->style_no ?? $inquiry->style_ref,
                'garment_description' => $inquiry->productType->name ?? RichText::plain($inquiry->description),
                'order_qty' => $inquiry->target_qty,
                'buyer_target_price' => $inquiry->target_price,
            ];
        }

        if ($request->filled('style_id') && ($style = Style::with(['productType', 'inquiry'])->find((int) $request->input('style_id')))) {
            $prefill = array_filter([
                'style_id' => $style->id,
                'inquiry_id' => $style->inquiry_id,
                'buyer_id' => $style->buyer_id,
                'style_ref' => $style->style_no,
                'garment_description' => $style->productType->name ?? $style->name,
                'smv' => $style->cost_smv ?? $style->smv,
                'cm_per_dozen' => $style->confirm_cm,
                'order_qty' => $style->inquiry?->target_qty,
                'buyer_target_price' => $style->inquiry?->target_price,
            ], fn ($v) => $v !== null) + $prefill;
        }

        return $prefill;
    }

    private function formOptions(): array
    {
        return [
            'stylesOptions' => Style::query()->active()->orderBy('style_no')->get(),
            'inquiriesOptions' => Inquiry::query()->with('buyer')->whereNotIn('status', ['lost', 'cancelled'])->latest('id')->get(),
            'buyersOptions' => Buyer::query()->active()->orderBy('name')->get(),
            'currenciesOptions' => Currency::query()->active()->orderBy('code')->get(),
            'itemsOptions' => Item::query()->active()->orderBy('name')->get(),
            'uomsOptions' => Uom::query()->active()->orderBy('name')->get(),
            'suppliersOptions' => Supplier::query()->active()->orderBy('name')->pluck('name', 'id'),
        ];
    }
}
