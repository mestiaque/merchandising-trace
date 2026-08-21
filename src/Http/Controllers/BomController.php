<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\BomRequest;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\Uom;

class BomController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_bom.list');

        $boms = Bom::query()
            ->with(['style.buyer'])
            ->withCount('items')
            ->when($request->filled('search'), fn ($q) => $q->whereHas('style', fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('style_no', 'like', '%' . $request->search . '%')))
            ->orderBy('style_id')
            ->orderByDesc('version')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.boms.index', compact('boms'));
    }

    public function printList(Request $request): View
    {
        $this->authorize('merch_bom.list');

        $boms = Bom::query()
            ->with(['style.buyer'])
            ->withCount('items')
            ->when($request->filled('search'), fn ($q) => $q->whereHas('style', fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('style_no', 'like', '%' . $request->search . '%')))
            ->orderBy('style_id')
            ->orderByDesc('version')
            ->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Bill of Materials',
            'columns' => ['style.style_no' => 'Style No', 'style.name' => 'Style', 'style.buyer.name' => 'Buyer', 'version' => 'Version', 'status' => 'Status'],
            'rows'    => $boms,
        ]);
    }

    public function create(): View
    {
        $this->authorize('merch_bom.add');

        return view('merchandising-trace::admin.boms.create', $this->formOptions());
    }

    public function store(BomRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $bom = DB::transaction(function () use ($data) {
            $nextVersion = Bom::where('style_id', $data['style_id'])->max('version') + 1;

            $bom = Bom::create([
                'style_id'   => $data['style_id'],
                'version'    => $nextVersion,
                'status'     => 'draft',
                'remarks'    => $data['remarks'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $line) {
                $line['waste_percent'] = $line['waste_percent'] ?? 0;
                $bom->items()->create($line);
            }

            return $bom;
        });

        return redirect()->route('merchandising-trace.boms.index')->with('success', "BOM v{$bom->version} for {$bom->style->name} created successfully.");
    }

    public function show(Bom $bom): View
    {
        $this->authorize('merch_bom.view');

        $bom->load(['style.buyer', 'items.unit']);

        return view('merchandising-trace::admin.boms.show', compact('bom'));
    }

    public function print(Bom $bom): View
    {
        $this->authorize('merch_bom.view');

        $bom->load(['style.buyer', 'items.unit']);

        return view('merchandising-trace::admin.partials.print-detail', [
            'title'    => 'Bill of Materials',
            'docNumber'=> ($bom->style->style_no ?? '') . ' — v' . $bom->version,
            'meta'     => [
                'Style'   => $bom->style->name ?? '-',
                'Buyer'   => $bom->style->buyer->name ?? '-',
                'Version' => $bom->version,
                'Status'  => ucfirst($bom->status),
            ],
            'sections' => [[
                'title'   => 'Material Lines',
                'columns' => ['item_type' => 'Item Type', 'material_name' => 'Material', 'consumption' => 'Consumption', 'waste_percent' => 'Waste %', 'unit.short_name' => 'UOM'],
                'rows'    => $bom->items->map(fn ($i) => (object) [
                    'item_type'     => ucfirst(str_replace('_', ' ', $i->item_type)),
                    'material_name' => $i->material_name,
                    'consumption'   => $i->consumption,
                    'waste_percent' => $i->waste_percent . '%',
                    'unit'          => $i->unit,
                ]),
            ]],
            'remarks' => $bom->remarks,
        ]);
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

        DB::transaction(function () use ($data, $bom) {
            $bom->update([
                'style_id' => $data['style_id'],
                'remarks'  => $data['remarks'] ?? null,
            ]);

            $bom->items()->delete();
            foreach ($data['items'] as $line) {
                $line['waste_percent'] = $line['waste_percent'] ?? 0;
                $bom->items()->create($line);
            }
        });

        return redirect()->route('merchandising-trace.boms.index')->with('success', 'BOM updated successfully.');
    }

    /**
     * BOM Version Control (merchandising.md §4): clones this version's
     * items into a new draft version for the same style, leaving history
     * intact rather than overwriting it in place.
     */
    public function newVersion(Bom $bom): RedirectResponse
    {
        $this->authorize('merch_bom.add');

        $new = $bom->cloneAsNewVersion();

        return redirect()->route('merchandising-trace.boms.edit', $new)->with('success', "Draft v{$new->version} created from v{$bom->version}.");
    }

    public function destroy(Bom $bom): RedirectResponse
    {
        $this->authorize('merch_bom.delete');

        $bom->delete();

        return back()->with('success', 'BOM deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'stylesOptions' => Style::query()->active()->orderBy('name')->get(),
            'unitsOptions'  => Uom::query()->active()->orderBy('name')->get(),
        ];
    }
}
