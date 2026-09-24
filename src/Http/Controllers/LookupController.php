<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\CostSheetItem;
use ME\MerchandisingTrace\Models\Inquiry;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Support\RichText;

/**
 * JSON used by the entry forms so a user picks a Style / Inquiry / Sales
 * Contract once and everything already known about it fills in — the same
 * information is never typed twice. Inquiry and SalesContract keep their
 * merchandiser row-scope through their global scopes.
 */
class LookupController extends Controller
{
    /** Anyone who can raise one of the documents that consume these lookups. */
    private const ABILITIES = [
        'merch_style.view', 'merch_inquiry.view', 'merch_costing.add', 'merch_costing.edit',
        'merch_bom.add', 'merch_bom.edit', 'merch_material_booking.add', 'merch_material_booking.edit',
    ];

    public function style(Style $style): JsonResponse
    {
        abort_unless(Gate::any(self::ABILITIES), 403);

        $style->load(['buyer', 'productType', 'washType', 'inquiry', 'images']);

        $costSheet = CostSheet::query()->where('style_id', $style->id)->with('items.item')
            ->orderByRaw("status = 'approved' desc")->latest('version')->first();

        // Prefer the approved BOM; only a manually built one has lines.
        $bom = Bom::query()->where('style_id', $style->id)->where('bom_type', 'manual')->with('items.item')
            ->orderByRaw("status = 'approved' desc")->latest('version')->first();

        return response()->json([
            'id' => $style->id,
            'style_no' => $style->style_no,
            'name' => $style->name,
            'buyer_id' => $style->buyer_id,
            'buyer_name' => $style->buyer->name ?? null,
            'garment_description' => $style->productType->name ?? $style->name,
            'wash_type' => $style->washType->name ?? null,
            'smv' => $style->cost_smv ?? $style->smv,
            'confirm_cm' => $style->confirm_cm,
            'inquiry' => $style->inquiry ? $this->inquiryPayload($style->inquiry) : null,
            'images' => $style->images->take(3)->map(fn ($img) => Storage::disk('public')->url($img->path))->values(),
            'cost_sheet' => $costSheet ? [
                'id' => $costSheet->id,
                'cost_sheet_no' => $costSheet->cost_sheet_no,
                'bom_lines' => $this->costSheetToBomLines($costSheet),
            ] : null,
            'bom' => $bom ? [
                'id' => $bom->id,
                'bom_no' => $bom->bom_no,
                'status' => $bom->status,
                'lines' => $bom->items->map(fn ($line) => [
                    'item_id' => $line->item_id,
                    'item_type' => $line->item_type,
                    'color_id' => $line->color_id,
                    'uom_id' => $line->uom_id,
                    'rate' => $line->rate,
                    'supplier_id' => $line->supplier_id,
                    'net_consumption' => round($line->netConsumption(), 6),
                ])->values(),
            ] : null,
        ]);
    }

    public function inquiry(Inquiry $inquiry): JsonResponse
    {
        abort_unless(Gate::any(self::ABILITIES), 403);

        $inquiry->load(['buyer', 'productType', 'techPack']);

        return response()->json($this->inquiryPayload($inquiry) + [
            'tech_pack' => $inquiry->techPack ? ['id' => $inquiry->techPack->id, 'style_no' => $inquiry->techPack->style_no] : null,
        ]);
    }

    /** Styles on the contract's POs, with the order qty for each. */
    public function salesContract(SalesContract $salesContract): JsonResponse
    {
        abort_unless(Gate::any(self::ABILITIES), 403);

        $salesContract->load(['buyer', 'pos.style']);

        $styles = $salesContract->pos->filter(fn ($po) => $po->style)->groupBy('style_id')->map(fn ($pos) => [
            'id' => $pos->first()->style->id,
            'style_no' => $pos->first()->style->style_no,
            'name' => $pos->first()->style->name,
            'order_qty' => $pos->sum(fn ($po) => $po->effectiveQty()),
        ])->values();

        return response()->json([
            'id' => $salesContract->id,
            'contract_no' => $salesContract->contract_no,
            'lc_no' => $salesContract->lc_no,
            'buyer_name' => $salesContract->buyer->name ?? null,
            'currency_id' => $salesContract->currency_id,
            'styles' => $styles,
        ]);
    }

    private function inquiryPayload(Inquiry $inquiry): array
    {
        return [
            'id' => $inquiry->id,
            'inquiry_no' => $inquiry->inquiry_no,
            'buyer_id' => $inquiry->buyer_id,
            'style_ref' => $inquiry->style_ref,
            'color_ref' => $inquiry->color_ref,
            'garment_description' => $inquiry->productType->name ?? RichText::plain($inquiry->description) ?: null,
            'order_qty' => $inquiry->target_qty,
            'unit_price' => $inquiry->target_price,
        ];
    }

    /**
     * Cost sheet (per dozen) → BOM lines (per piece). Only fabric & trims
     * lines are materials; wash/print/etc. are processes, not BOM items.
     */
    private function costSheetToBomLines(CostSheet $costSheet): array
    {
        $suppliers = Supplier::query()->pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [mb_strtolower(trim($name)) => $id]);

        return $costSheet->items
            ->whereIn('group', ['fabric', 'trims'])
            ->map(fn (CostSheetItem $line) => [
                'item_id' => $line->item_id,
                'part_name' => $line->label(),
                'consumption' => round($line->perPieceConsumption(), 4),
                'uom_id' => $line->uom_id ?? $line->item?->uom_id,
                'rate' => $line->rate,
                'supplier_id' => $suppliers[mb_strtolower(trim((string) $line->supplier_name))] ?? $line->item?->default_supplier_id,
            ])->values()->all();
    }
}
