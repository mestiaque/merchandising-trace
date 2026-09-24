<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\BomRequest;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Models\Uom;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class BomController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_bom.list');

        $boms = Bom::query()
            ->with(['style'])
            ->when($request->filled('search'), fn ($q) => $q->where('bom_no', 'like', '%' . $request->search . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.boms.index', ['boms' => $boms]);
    }

    public function create(): View
    {
        $this->authorize('merch_bom.add');

        return view('merchandising-trace::admin.boms.create', $this->formOptions());
    }

    public function store(BomRequest $request, DocumentNumberService $numbers): RedirectResponse
    {
        $data = $request->validated();

        $bom = DB::transaction(function () use ($data, $request, $numbers) {
            $version = Bom::where('style_id', $data['style_id'])->max('version');

            $bom = Bom::create([
                'bom_no' => $numbers->next(Bom::class, 'bom_no', config('merchandising-trace.document_prefixes.bom', 'BOM')),
                'style_id' => $data['style_id'],
                'version' => ($version ?? 0) + 1,
                'bom_type' => $data['bom_type'],
                'status' => 'draft',
                'remarks' => $data['remarks'] ?? null,
                'bom_file' => $data['bom_type'] === 'file' && $request->hasFile('bom_file')
                    ? $request->file('bom_file')->store('merchandising-trace/bom-files', 'public')
                    : null,
                'created_by' => auth()->id(),
            ]);

            if ($bom->isManual()) {
                $this->saveItems($bom, $data['items']);
            }

            return $bom;
        });

        return redirect()->route('merchandising-trace.boms.show', $bom)->with('success', "BOM {$bom->bom_no} created successfully.");
    }

    public function show(Bom $bom): View
    {
        $this->authorize('merch_bom.view');

        $bom->load(['style.buyer', 'approver', 'items.item', 'items.color', 'items.size', 'items.uom', 'items.supplier']);

        return view('merchandising-trace::admin.boms.show', compact('bom'));
    }

    public function edit(Bom $bom): View
    {
        $this->authorize('merch_bom.edit');

        $bom->load('items');

        return view('merchandising-trace::admin.boms.edit', ['bom' => $bom] + $this->formOptions());
    }

    public function update(BomRequest $request, Bom $bom): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $bom) {
            $update = ['bom_type' => $data['bom_type'], 'remarks' => $data['remarks'] ?? null];
            if ($data['bom_type'] === 'file' && $request->hasFile('bom_file')) {
                $update['bom_file'] = $request->file('bom_file')->store('merchandising-trace/bom-files', 'public');
            }
            $bom->update($update);

            // Lines only exist for a manual BOM; a stored PDF is kept either
            // way so switching back and forth never silently loses a file.
            $bom->items()->delete();
            if ($bom->isManual()) {
                $this->saveItems($bom, $data['items']);
            }
        });

        return redirect()->route('merchandising-trace.boms.show', $bom)->with('success', 'BOM updated successfully.');
    }

    public function destroy(Bom $bom): RedirectResponse
    {
        $this->authorize('merch_bom.delete');

        $bom->delete();

        return redirect()->route('merchandising-trace.boms.index')->with('success', 'BOM deleted successfully.');
    }

    public function approve(Bom $bom): RedirectResponse
    {
        $this->authorize('merch_bom.edit');

        $bom->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', "BOM {$bom->bom_no} approved.");
    }

    public function viewFile(Bom $bom)
    {
        $this->authorize('merch_bom.view');

        abort_unless($bom->bom_file, 404);

        return Storage::disk('public')->response($bom->bom_file);
    }

    public function downloadFile(Bom $bom)
    {
        $this->authorize('merch_bom.view');

        abort_unless($bom->bom_file, 404);

        return Storage::disk('public')->download($bom->bom_file);
    }

    private function saveItems(Bom $bom, array $items): void
    {
        foreach ($items as $line) {
            $item = Item::findOrFail($line['item_id']);

            $bom->items()->create([
                'item_id' => $item->id,
                'item_type' => $item->type,
                'color_id' => $line['color_id'] ?? null,
                'size_id' => $line['size_id'] ?? null,
                'part_name' => $line['part_name'] ?? null,
                'consumption' => $line['consumption'],
                'uom_id' => $line['uom_id'] ?? $item->uom_id,
                'wastage_percent' => $line['wastage_percent'] ?? 0,
                'rate' => $line['rate'] ?? $item->default_price,
                'supplier_id' => $line['supplier_id'] ?? $item->default_supplier_id,
                'lead_time_days' => $line['lead_time_days'] ?? null,
            ]);
        }
    }

    private function formOptions(): array
    {
        return [
            'stylesOptions' => Style::query()->active()->with('buyer')->orderBy('name')->get(),
            'itemsOptions' => Item::query()->active()->orderBy('name')->get(),
            'uomsOptions' => Uom::query()->active()->orderBy('name')->get(),
            'suppliersOptions' => Supplier::query()->active()->orderBy('name')->get(),
            'colorsOptions' => Color::query()->active()->orderBy('name')->get(),
            'sizesOptions' => Size::query()->active()->orderBy('sort_order')->get(),
        ];
    }
}
