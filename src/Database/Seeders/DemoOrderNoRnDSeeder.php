<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use ME\MerchandisingTrace\Database\Seeders\Concerns\SeedsDemoMasters;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\CommunicationLog;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\FabricConsumption;
use ME\MerchandisingTrace\Models\Inquiry;
use ME\MerchandisingTrace\Models\InquiryItem;
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\StylePart;
use ME\MerchandisingTrace\Services\DocumentChecklistService;
use ME\MerchandisingTrace\Services\MaterialBookingTnaSyncService;
use ME\MerchandisingTrace\Services\PcdGateService;
use ME\MerchandisingTrace\Services\ProductionHandoverService;
use ME\MerchandisingTrace\Services\ProductionProgressSyncService;
use ME\MerchandisingTrace\Services\TnaPlanGenerationService;

/**
 * Demo order type 1 — "Order-e R&D lagbe na": buyer hands over an
 * already-approved sample/tech pack, so the style is flagged
 * requires_dev_sample=false and NO Fit/PP1/WashStd sample rows are ever
 * created for it. TnaPlanGenerationService pre-marks every sample-sourced
 * T&A task n/a for such styles (see its own docblock), so PCD still
 * reaches PASS purely from the Pilot/Fabric/Trims tasks below. Everything
 * downstream of Dev (BOM, Costing, Purchase, Handover) still runs in full.
 */
class DemoOrderNoRnDSeeder extends Seeder
{
    use SeedsDemoMasters;

    private const CONTRACT_NO = 'DEMO-CORVEX-NORND';
    private const STYLE_NO = 'RUE-NORND';

    public function run(): void
    {
        if (SalesContract::where('contract_no', self::CONTRACT_NO)->exists()) {
            $this->command?->info('Demo order (no R&D) already exists — skipping.');

            return;
        }

        (new TrimsItemSeeder())->run();
        (new DefaultTnaTemplateSeeder())->run();
        (new DefaultDocumentTemplateSeeder())->run();
        (new SampleTypeSeeder())->run();

        $merchandiser = User::query()->first();
        $m = $this->demoMasters($merchandiser?->id);
        $trc = $this->trcIds();
        extract($m);
        extract($trc);

        $inquiry = Inquiry::create([
            'inquiry_no' => 'INQ-DEMO-NORND-' . now()->format('ymd'),
            'inquiry_given_date' => now()->subDays(40)->toDateString(),
            'buyer_id' => $buyer->id, 'season_id' => $season->id, 'merchandiser_id' => $merchandiser?->id,
            'factory_id' => $factory->id, 'order_confirmation_due_date' => now()->subDays(20)->toDateString(),
            'product_type_id' => $productType->id, 'description' => 'Buyer-approved sample — no in-house Dev stage',
            'target_qty' => 8000, 'target_price' => 4.75, 'target_ship_date' => now()->addDays(75)->toDateString(),
            'status' => 'confirmed',
        ]);
        InquiryItem::create(['inquiry_id' => $inquiry->id, 'style_ref' => self::STYLE_NO, 'product_type_id' => $productType->id, 'color_ref' => 'Black', 'qty' => 8000, 'target_price' => 4.75]);

        $style = Style::create([
            'style_no' => self::STYLE_NO, 'name' => self::STYLE_NO . ' - Unisex Jacket - Black',
            'description' => 'Buyer supplied an already-approved sample — Dev stage skipped',
            'buyer_id' => $buyer->id, 'inquiry_id' => $inquiry->id, 'season_id' => $season->id,
            'merchandiser_id' => $merchandiser?->id, 'wash_type_id' => $washType->id, 'product_type_id' => $productType->id,
            'trc_product_id' => $trcProductId, 'trc_size_group_id' => $trcSizeGroupId,
            'smv' => 55.00, 'cost_smv' => 58.84, 'target_cm' => 3.00,
            'fabric_description' => 'Twill 60% cotton / 40% polyester, 240 GSM', 'development_status' => 'approved',
            'requires_dev_sample' => false, 'fabric_sourced_by' => 'self',
            'is_active' => true, 'created_by' => $merchandiser?->id,
        ]);

        $style->images()->create(['path' => 'demo/rue-norrnd-buyer-sample.jpg', 'type' => 'front', 'caption' => 'Buyer-approved sample photo']);
        $chest = $style->measurements()->create(['pom_code' => 'CH', 'pom_name' => 'Chest Width', 'tolerance_plus' => 0.5, 'tolerance_minus' => 0.5, 'sort_order' => 1]);
        $chest->sizes()->create(['size_id' => $sizeS->id, 'value' => 21.0]);
        $chest->sizes()->create(['size_id' => $sizeM->id, 'value' => 22.0]);
        $chest->sizes()->create(['size_id' => $sizeL->id, 'value' => 23.0]);
        $chest->sizes()->create(['size_id' => $sizeXl->id, 'value' => 24.0]);

        StylePart::create(['style_id' => $style->id, 'trc_part_id' => $trcPartId, 'qty_per_garment' => 1, 'embellishment_type' => 'heat_seal', 'placement' => 'Left chest', 'is_critical' => true]);
        $style->operations()->create(['operation_name' => 'Sleeve set', 'machine_type' => 'Overlock', 'smv' => 1.20, 'sequence' => 1]);

        FabricConsumption::create(['style_id' => $style->id, 'color_id' => $color->id, 'item_id' => $fabricItem->id, 'yy' => 2.52, 'marker_efficiency' => 82.5, 'gsm' => 240, 'width' => 58, 'calculated_by' => $merchandiser?->id, 'method' => 'marker']);

        // --- No samples created for this style (requires_dev_sample = false) ---

        // --- BOM ---
        $bom = Bom::create(['bom_no' => 'BOM-DEMONORND', 'style_id' => $style->id, 'version' => 1, 'status' => 'approved', 'approved_by' => $merchandiser?->id, 'approved_at' => now()->subDays(30), 'created_by' => $merchandiser?->id]);
        $bom->items()->create(['item_id' => $fabricItem->id, 'item_type' => 'fabric', 'color_id' => $color->id, 'part_name' => 'Body', 'consumption' => 2.52, 'uom_id' => $uomYard->id, 'wastage_percent' => 3, 'rate' => 3.20, 'currency_id' => $currency->id, 'supplier_id' => $fabricSupplier->id, 'lead_time_days' => 45]);
        foreach (['THREAD' => 1, 'ZIPPER' => 1, 'MAIN_LABEL' => 1, 'SIZE_LABEL' => 1, 'CARE_LABEL' => 1] as $code => $qty) {
            $trim = Item::where('code', $code)->firstOrFail();
            $bom->items()->create(['item_id' => $trim->id, 'item_type' => 'trim', 'part_name' => $trim->name, 'consumption' => $qty, 'uom_id' => $trim->uom_id, 'wastage_percent' => 2, 'rate' => $trim->default_price ?? 0.05, 'supplier_id' => $trimsSupplier->id, 'lead_time_days' => 15]);
        }

        // --- Costing ---
        $costSheet = CostSheet::create(['cost_sheet_no' => 'CST-DEMONORND', 'style_id' => $style->id, 'buyer_id' => $buyer->id, 'version' => 1, 'currency_id' => $currency->id, 'exchange_rate' => 1, 'order_qty' => 8000, 'smv' => 58.84, 'cm_minute_rate' => 0.037, 'efficiency_percent' => 72.5, 'fabric_cost' => 2.52 * 3.20 * 1.03, 'trims_cost' => 0.35, 'accessories_cost' => 0.10, 'print_emb_cost' => 0, 'wash_cost' => 0.18, 'commercial_cost' => 0.08, 'freight_cost' => 0.05, 'testing_cost' => 0.02, 'overhead_cost' => 0.12, 'profit_percent' => 12, 'price_type' => 'FOB', 'buyer_target_price' => 4.75, 'status' => 'approved', 'prepared_by' => $merchandiser?->id, 'approved_by' => $merchandiser?->id, 'approved_at' => now()->subDays(25)]);
        $costSheet->recompute();

        // --- Sales Contract / PO ---
        $contract = SalesContract::create(['contract_no' => self::CONTRACT_NO, 'buyer_id' => $buyer->id, 'season_id' => $season->id, 'merchandiser_id' => $merchandiser?->id, 'factory_id' => $factory->id, 'inquiry_id' => $inquiry->id, 'buyer_order_ref' => 'CORVEX-PO-NORND', 'contract_date' => now()->subDays(20)->toDateString(), 'currency_id' => $currency->id, 'exchange_rate' => 1, 'delivery_term' => 'FOB', 'payment_term' => 'LC at sight', 'lc_no' => 'LC-DEMONORND', 'lc_date' => now()->subDays(15)->toDateString(), 'lc_value' => 38000, 'lc_expiry' => now()->addDays(60)->toDateString(), 'status' => 'draft', 'created_by' => $merchandiser?->id]);

        $po = SalesContractPo::create(['sales_contract_id' => $contract->id, 'style_id' => $style->id, 'product_type_id' => $productType->id, 'color_id' => $color->id, 'wash_type_id' => $washType->id, 'po_no' => 'PO-NORND-0001', 'po_due_date' => now()->subDays(18)->toDateString(), 'po_qty' => 8000, 'unit_price' => 4.75, 'total_value' => 8000 * 4.75, 'price_type' => 'FOB', 'cost_smv' => 58.84, 'cm' => 3.00, 'fob_foc' => 4.75, 'pcd_date' => now()->addDays(3)->toDateString(), 'shipment_date' => now()->addDays(30)->toDateString(), 'ship_mode_id' => $shipMode->id, 'print_emb' => 'no', 'emb_applique_ih' => 'no', 'studs_stones_ih' => 'no', 'heat_seal_ih' => 'yes', 'status' => 'pending']);
        foreach ([$sizeS->id => 2000, $sizeM->id => 2500, $sizeL->id => 2500, $sizeXl->id => 1000] as $sizeId => $qty) {
            $po->sizes()->create(['size_id' => $sizeId, 'qty' => $qty]);
        }

        $contract->update(['status' => 'confirmed']);
        $contract->refreshTotals();

        $tnaPlan = app(TnaPlanGenerationService::class)->generateFor($po);
        $po->update(['status' => 'tna_created']);
        app(DocumentChecklistService::class)->generateFor($contract);

        $fileHandoverTask = $tnaPlan->tasks()->where('task_code', 'file_handover')->first();
        $fileHandoverTask->update(['actual_date' => now()->subDays(2), 'status' => 'done']);
        foreach (['pullout', 'pp_meeting'] as $code) {
            $tnaPlan->tasks()->where('task_code', $code)->update(['actual_date' => now()->subDays(1), 'status' => 'done']);
        }

        // --- Material Booking (purchase still needed for this order) ---
        $syncService = app(MaterialBookingTnaSyncService::class);

        $fabricBooking = MaterialBooking::create(['booking_no' => 'MB-DEMONORND-FABRIC', 'type' => 'fabric', 'sales_contract_id' => $contract->id, 'sales_contract_po_id' => $po->id, 'style_id' => $style->id, 'supplier_id' => $fabricSupplier->id, 'mill_country' => 'China', 'booking_date' => now()->subDays(16)->toDateString(), 'pi_no' => 'PI-DEMONORND', 'pi_date' => now()->subDays(16)->toDateString(), 'pi_value' => 20200 * 3.20, 'currency_id' => $currency->id, 'lc_no' => 'LC-DEMONORND', 'lc_date' => now()->subDays(12)->toDateString(), 'lc_value' => 20200 * 3.20, 'lc_type' => 'LC', 'x_mill_date' => now()->subDays(8)->toDateString(), 'expected_inhouse_date' => now()->subDays(2)->toDateString(), 'status' => 'received']);
        $fabricBooking->items()->create(['item_id' => $fabricItem->id, 'color_id' => $color->id, 'booked_qty' => 20200, 'uom_id' => $uomYard->id, 'rate' => 3.20]);
        $syncService->syncFromBooking($fabricBooking);

        $consignment = $fabricBooking->consignments()->create(['consignment_no' => 1, 'planned_date' => now()->subDays(8)->toDateString(), 'planned_qty' => 20200]);
        $consignment->update(['actual_date' => now()->subDays(7)->toDateString(), 'received_qty' => 20200, 'challan_no' => 'CH-DEMONORND-1', 'invoice_no' => 'INV-DEMONORND-1', 'status' => 'received']);
        $syncService->syncFromConsignment($consignment);
        $fabricBooking->receipts()->create(['consignment_id' => $consignment->id, 'item_id' => $fabricItem->id, 'receive_date' => $consignment->actual_date, 'qty' => 20200, 'store_ref' => 'STORE-NORND-FAB', 'received_by' => $merchandiser?->id]);

        $trimsBooking = MaterialBooking::create(['booking_no' => 'MB-DEMONORND-TRIMS', 'type' => 'trims', 'sales_contract_id' => $contract->id, 'sales_contract_po_id' => $po->id, 'style_id' => $style->id, 'supplier_id' => $trimsSupplier->id, 'booking_date' => now()->subDays(10)->toDateString(), 'expected_inhouse_date' => now()->subDays(2)->toDateString(), 'status' => 'received']);
        foreach (['THREAD', 'ZIPPER', 'MAIN_LABEL', 'SIZE_LABEL', 'CARE_LABEL', 'ELASTICS', 'BUTTONS', 'VELCRO'] as $code) {
            $trim = Item::where('code', $code)->firstOrFail();
            $trimsBooking->items()->create(['item_id' => $trim->id, 'booked_qty' => 8500, 'uom_id' => $trim->uom_id, 'rate' => $trim->default_price ?? 0.05]);
            $receipt = $trimsBooking->receipts()->create(['item_id' => $trim->id, 'receive_date' => now()->subDays(3)->toDateString(), 'qty' => 8500, 'store_ref' => "STORE-NORND-{$code}", 'received_by' => $merchandiser?->id]);
            $syncService->syncFromReceipt($receipt);
        }

        app(PcdGateService::class)->evaluate($tnaPlan->fresh());
        $tnaPlan->refresh();

        $style->update(['trc_product_id' => $trcProductId, 'trc_size_group_id' => $trcSizeGroupId]);

        $handover = app(ProductionHandoverService::class)->push($po->fresh(), $merchandiser?->id ?? 1);

        $line = \ME\MerchandisingTrace\Models\Bridge\TrcPlanLine::with('sizes')->find($handover->plan_line_id);
        $qtyPerSize = ['S' => 2000, 'M' => 2500, 'L' => 2500, 'XL' => 1000];
        foreach ($line->sizes as $lineSize) {
            $sizeName = Size::find($lineSize->size_id)?->name;
            $q = $qtyPerSize[$sizeName] ?? 0;
            $lineSize->update(['cut_qty' => $q, 'sewn_qty' => (int) ($q * 0.5), 'finished_qty' => (int) ($q * 0.2), 'passed_qty' => (int) ($q * 0.18), 'approved_qty' => (int) ($q * 0.18), 'packed_qty' => 0, 'shipped_qty' => 0, 'reject_qty' => (int) ($q * 0.02)]);
        }
        app(ProductionProgressSyncService::class)->syncFor($po->fresh());

        CommunicationLog::create(['style_id' => $style->id, 'sales_contract_po_id' => $po->id, 'log_date' => now()->subDays(19)->toDateString(), 'direction' => 'inbound', 'channel' => 'email', 'subject' => 'Sample pre-approved — proceed directly to bulk', 'body' => 'We already approved this style in a prior season. Please skip PP and proceed to bulk production.', 'created_by' => $merchandiser?->id]);

        $this->command?->info('Demo order (no R&D) seeded: ' . self::CONTRACT_NO . ' — PCD ' . strtoupper($tnaPlan->fresh()->pcd_result) . ', handed over to production.');
    }
}
