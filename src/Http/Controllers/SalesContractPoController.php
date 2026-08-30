<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\SalesContractPoRequest;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\ProductType;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\ShipMode;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\WashType;
use ME\MerchandisingTrace\Imports\TnaGridImport;
use ME\MerchandisingTrace\Services\SalesContractPoImportService;

class SalesContractPoController extends Controller
{
    public function create(SalesContract $salesContract): View
    {
        $this->authorize('merch_sales_contract.add');

        return view('merchandising-trace::admin.sales-contracts.pos.create', ['salesContract' => $salesContract] + $this->formOptions($salesContract->buyer_id));
    }

    public function store(SalesContractPoRequest $request, SalesContract $salesContract): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $salesContract) {
            $po = $salesContract->pos()->create([
                'style_id' => $data['style_id'],
                'product_type_id' => $data['product_type_id'] ?? null,
                'color_id' => $data['color_id'],
                'wash_type_id' => $data['wash_type_id'] ?? null,
                'po_no' => $data['po_no'],
                'po_due_date' => $data['po_due_date'] ?? null,
                'po_qty' => $data['po_qty'],
                'unit_price' => $data['unit_price'] ?? null,
                'total_value' => ($data['unit_price'] ?? 0) * $data['po_qty'],
                'price_type' => $data['price_type'] ?? null,
                'cost_smv' => $data['cost_smv'] ?? null,
                'cm' => $data['cm'] ?? null,
                'fob_foc' => $data['fob_foc'] ?? null,
                'pcd_date' => $data['pcd_date'] ?? null,
                'shipment_date' => $data['shipment_date'] ?? null,
                'ship_mode_id' => $data['ship_mode_id'] ?? null,
                'print_emb' => $data['print_emb'] ?? 'na',
                'emb_applique_ih' => $data['emb_applique_ih'] ?? 'na',
                'studs_stones_ih' => $data['studs_stones_ih'] ?? 'na',
                'heat_seal_ih' => $data['heat_seal_ih'] ?? 'na',
                'status' => 'pending',
                'remarks' => $data['remarks'] ?? null,
            ]);

            foreach ($data['sizes'] as $line) {
                if ((int) $line['qty'] <= 0) {
                    continue;
                }
                $po->sizes()->create($line);
            }

            $salesContract->refreshTotals();
        });

        return redirect()->route('merchandising-trace.sales-contracts.show', $salesContract)->with('success', 'PO line added successfully.');
    }

    public function edit(SalesContract $salesContract, SalesContractPo $salesContractPo): View
    {
        $this->authorize('merch_sales_contract.edit');
        $this->assertBelongsToContract($salesContract, $salesContractPo);

        $salesContractPo->load(['sizes', 'revisions.changer']);

        return view('merchandising-trace::admin.sales-contracts.pos.edit', [
            'salesContract' => $salesContract,
            'salesContractPo' => $salesContractPo,
        ] + $this->formOptions($salesContract->buyer_id));
    }

    public function update(SalesContractPoRequest $request, SalesContract $salesContract, SalesContractPo $salesContractPo): RedirectResponse
    {
        $this->assertBelongsToContract($salesContract, $salesContractPo);
        $data = $request->validated();

        DB::transaction(function () use ($data, $salesContract, $salesContractPo) {
            $salesContractPo->update([
                'style_id' => $data['style_id'],
                'product_type_id' => $data['product_type_id'] ?? null,
                'color_id' => $data['color_id'],
                'wash_type_id' => $data['wash_type_id'] ?? null,
                'po_no' => $data['po_no'],
                'po_due_date' => $data['po_due_date'] ?? null,
                'unit_price' => $data['unit_price'] ?? null,
                'total_value' => ($data['unit_price'] ?? 0) * $salesContractPo->effectiveQty(),
                'price_type' => $data['price_type'] ?? null,
                'cost_smv' => $data['cost_smv'] ?? null,
                'cm' => $data['cm'] ?? null,
                'fob_foc' => $data['fob_foc'] ?? null,
                'ship_mode_id' => $data['ship_mode_id'] ?? null,
                'print_emb' => $data['print_emb'] ?? 'na',
                'emb_applique_ih' => $data['emb_applique_ih'] ?? 'na',
                'studs_stones_ih' => $data['studs_stones_ih'] ?? 'na',
                'heat_seal_ih' => $data['heat_seal_ih'] ?? 'na',
                'remarks' => $data['remarks'] ?? null,
                // Qty/PCD/Shipment revisions handled via the dedicated
                // reviseQty/revisePcd/reviseShipment actions (§6 Rule 2:
                // every revision needs a reason, logged).
            ]);

            $salesContractPo->sizes()->delete();
            foreach ($data['sizes'] as $line) {
                if ((int) $line['qty'] <= 0) {
                    continue;
                }
                $salesContractPo->sizes()->create($line);
            }

            $salesContract->refreshTotals();
        });

        return redirect()->route('merchandising-trace.sales-contracts.show', $salesContract)->with('success', 'PO line updated successfully.');
    }

    /**
     * §M07 AC: "Excel import" for the PO grid.
     */
    public function importExcel(Request $request, SalesContract $salesContract, SalesContractPoImportService $importer): RedirectResponse
    {
        $this->authorize('merch_sales_contract.add');

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new TnaGridImport(), $request->file('file'));

        try {
            $result = $importer->import($salesContract, $sheets[0] ?? [], auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = "{$result['created']} PO line(s) imported.";
        if ($result['skipped']) {
            $message .= ' Skipped: ' . implode('; ', $result['skipped']);
        }

        return redirect()->route('merchandising-trace.sales-contracts.show', $salesContract)->with('success', $message);
    }

    public function destroy(SalesContract $salesContract, SalesContractPo $salesContractPo): RedirectResponse
    {
        $this->authorize('merch_sales_contract.delete');
        $this->assertBelongsToContract($salesContract, $salesContractPo);

        $salesContractPo->delete();
        $salesContract->refreshTotals();

        return back()->with('success', 'PO line deleted successfully.');
    }

    /**
     * Order-wise PO document — buyer-facing print, same layout family as
     * the Cost Sheet PDF (§M06) and the Risk Assessment register.
     */
    public function pdf(SalesContract $salesContract, SalesContractPo $salesContractPo)
    {
        $this->authorize('merch_sales_contract.view');
        $this->assertBelongsToContract($salesContract, $salesContractPo);

        $salesContractPo->load(['style', 'productType', 'color', 'shipMode', 'sizes']);
        $salesContract->load(['buyer', 'season']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('merchandising-trace::admin.sales-contracts.pos.pdf', [
            'salesContract' => $salesContract,
            'po' => $salesContractPo,
            'sizes' => Size::query()->active()->orderBy('sort_order')->get(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("PO-{$salesContractPo->po_no}.pdf");
    }

    /**
     * §6 Rule 2: every qty/PCD/shipment revision requires a reason and is
     * logged. $field is one of po_qty|pcd|shipment; revision slot 1 is
     * filled first, then slot 2 — never overwriting a value already set.
     */
    public function revise(Request $request, SalesContract $salesContract, SalesContractPo $salesContractPo): RedirectResponse
    {
        $this->authorize('merch_sales_contract.edit');
        $this->assertBelongsToContract($salesContract, $salesContractPo);

        $request->validate([
            'field' => ['required', 'string', 'in:po_qty,pcd,shipment'],
            'value' => ['required'],
            'reason' => ['required', 'string'],
        ]);

        $columnMap = [
            'po_qty' => ['po_qty_revised_1', 'po_qty_revised_2'],
            'pcd' => ['pcd_revised_1', 'pcd_revised_2'],
            'shipment' => ['shipment_revised_1', 'shipment_revised_2'],
        ];
        [$slot1, $slot2] = $columnMap[$request->field];
        $targetColumn = $salesContractPo->{$slot1} === null ? $slot1 : $slot2;
        $oldValue = $salesContractPo->{$targetColumn};

        $salesContractPo->update([$targetColumn => $request->value]);

        $salesContractPo->revisions()->create([
            'field' => $request->field,
            'old_value' => $oldValue,
            'new_value' => $request->value,
            'reason' => $request->reason,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        if ($request->field === 'po_qty') {
            $salesContract->refreshTotals();
        }

        return back()->with('success', 'Revision recorded.');
    }

    /**
     * Laravel's implicit route-model binding resolves {sales_contract} and
     * {sales_contract_po} independently by their own primary keys — it does
     * NOT verify the PO actually belongs to that contract. Without this
     * guard, a merchandiser who owns *some* contract (so the ScopedToMerchandiser
     * scope on SalesContract lets that ID resolve) could edit/delete/view any
     * OTHER merchandiser's PO line just by pairing their own contract ID
     * with someone else's PO ID in the URL — an IDOR, since SalesContractPo
     * itself has no merchandiser_id column to scope directly (§3's row-level
     * scoping is enforced at the SalesContract level and assumed to cover
     * its children transitively).
     */
    private function assertBelongsToContract(SalesContract $salesContract, SalesContractPo $salesContractPo): void
    {
        abort_unless($salesContractPo->sales_contract_id === $salesContract->id, 404);
    }

    /**
     * §14/2: a PO must only ever attach a style belonging to the same
     * buyer as its sales contract — the style dropdown is scoped
     * accordingly, in addition to the FormRequest rule.
     */
    private function formOptions(?int $buyerId = null): array
    {
        return [
            'stylesOptions' => Style::query()->active()
                ->when($buyerId, fn ($q) => $q->where('buyer_id', $buyerId))
                ->orderBy('name')->get(),
            'productTypesOptions' => ProductType::query()->active()->orderBy('name')->get(),
            'colorsOptions' => Color::query()->active()->orderBy('name')->get(),
            'washTypesOptions' => WashType::query()->active()->orderBy('name')->get(),
            'shipModesOptions' => ShipMode::query()->active()->orderBy('name')->get(),
            'sizesOptions' => Size::query()->active()->orderBy('sort_order')->get(),
        ];
    }
}
