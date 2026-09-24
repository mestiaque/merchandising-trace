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
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\OrderDocument;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\SampleType;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\StylePart;
use ME\MerchandisingTrace\Models\TnaSubPlan;
use ME\MerchandisingTrace\Services\DocumentChecklistService;
use ME\MerchandisingTrace\Services\MaterialBookingTnaSyncService;
use ME\MerchandisingTrace\Services\PcdGateService;
use ME\MerchandisingTrace\Services\ProductionHandoverService;
use ME\MerchandisingTrace\Services\ProductionProgressSyncService;
use ME\MerchandisingTrace\Services\SampleTnaSyncService;
use ME\MerchandisingTrace\Services\TnaAlertService;
use ME\MerchandisingTrace\Services\TnaPlanGenerationService;

/**
 * Demo order type 2 — "R&D theke suru hobe (full process)": the complete
 * pipeline, style flagged requires_dev_sample=true / fabric_sourced_by=self
 * (both defaults), walked end to end exactly like §13 Appendix C — Dev
 * (Fit + PP1 + Wash Std samples) -> BOM -> Costing -> Sales Contract ->
 * T&A (fail then pass) -> Sub-T&A -> Material Booking (fabric + trims,
 * fully received) -> Production Handover -> reverse progress -> Shipment
 * -> Documentation -> Buyer Communication.
 */
class DemoOrderFullProcessSeeder extends Seeder
{
    use SeedsDemoMasters;

    private const CONTRACT_NO = 'DEMO-CORVEX-FULL';
    private const STYLE_NO = 'RUE-FULL';

    public function run(): void
    {
        if (SalesContract::where('contract_no', self::CONTRACT_NO)->exists()) {
            $this->command?->info('Demo order (full process) already exists — skipping.');

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
            'inquiry_no' => 'INQ-DEMO-FULL-' . now()->format('ymd'),
            'inquiry_given_date' => now()->subDays(60)->toDateString(),
            'buyer_id' => $buyer->id, 'season_id' => $season->id, 'merchandiser_id' => $merchandiser?->id,
            'factory_id' => $factory->id, 'order_confirmation_due_date' => now()->subDays(30)->toDateString(),
            'product_type_id' => $productType->id, 'description' => 'Unisex jacket, full Dev + Purchase pipeline',
            'style_ref' => self::STYLE_NO, 'color_ref' => 'Black',
            'target_qty' => 10000, 'target_price' => 4.82, 'target_ship_date' => now()->addDays(90)->toDateString(),
            'status' => 'confirmed',
        ]);

        $style = Style::create([
            'style_no' => self::STYLE_NO, 'name' => self::STYLE_NO . ' - Unisex Jacket - Black',
            'description' => 'Unisex jacket with heat-seal chest branding — full Dev + Purchase demo',
            'buyer_id' => $buyer->id, 'inquiry_id' => $inquiry->id, 'season_id' => $season->id,
            'merchandiser_id' => $merchandiser?->id, 'wash_type_id' => $washType->id, 'product_type_id' => $productType->id,
            'trc_product_id' => $trcProductId, 'trc_size_group_id' => $trcSizeGroupId,
            'smv' => 55.00, 'cost_smv' => 58.84, 'target_cm' => 3.00,
            'fabric_description' => 'Twill 60% cotton / 40% polyester, 240 GSM', 'development_status' => 'sample_stage',
            'requires_dev_sample' => true, 'fabric_sourced_by' => 'self',
            'is_active' => true, 'created_by' => $merchandiser?->id,
        ]);

        $style->images()->create(['path' => 'demo/rue-full-front.jpg', 'type' => 'front', 'caption' => 'Front view']);
        $chest = $style->measurements()->create(['pom_code' => 'CH', 'pom_name' => 'Chest Width', 'tolerance_plus' => 0.5, 'tolerance_minus' => 0.5, 'sort_order' => 1]);
        $chest->sizes()->create(['size_id' => $sizeS->id, 'value' => 21.0]);
        $chest->sizes()->create(['size_id' => $sizeM->id, 'value' => 22.0]);
        $chest->sizes()->create(['size_id' => $sizeL->id, 'value' => 23.0]);
        $chest->sizes()->create(['size_id' => $sizeXl->id, 'value' => 24.0]);

        StylePart::create(['style_id' => $style->id, 'trc_part_id' => $trcPartId, 'qty_per_garment' => 1, 'embellishment_type' => 'heat_seal', 'placement' => 'Left chest', 'is_critical' => true]);
        $style->operations()->create(['operation_name' => 'Sleeve set', 'machine_type' => 'Overlock', 'smv' => 1.20, 'sequence' => 1]);

        FabricConsumption::create(['style_id' => $style->id, 'color_id' => $color->id, 'item_id' => $fabricItem->id, 'yy' => 2.52, 'marker_efficiency' => 82.5, 'gsm' => 240, 'width' => 58, 'calculated_by' => $merchandiser?->id, 'method' => 'marker']);

        // --- Dev: samples ---
        $ppType = SampleType::where('code', 'PP1')->firstOrFail();
        $fitType = SampleType::where('code', 'FIT')->firstOrFail();
        $washStdType = SampleType::where('code', 'WASHSTD')->firstOrFail();

        $fitSample = Sample::create(['sample_no' => 'SMP-DEMOFULL-FIT', 'style_id' => $style->id, 'buyer_id' => $buyer->id, 'sample_type_id' => $fitType->id, 'merchandiser_id' => $merchandiser?->id, 'request_date' => now()->subDays(55)->toDateString(), 'required_date' => now()->subDays(50)->toDateString(), 'qty' => 2, 'size_ref' => 'M', 'submit_date' => now()->subDays(52)->toDateString(), 'approval_date' => now()->subDays(48)->toDateString(), 'status' => 'approved']);
        $ppSample = Sample::create(['sample_no' => 'SMP-DEMOFULL-PP1', 'style_id' => $style->id, 'buyer_id' => $buyer->id, 'sample_type_id' => $ppType->id, 'merchandiser_id' => $merchandiser?->id, 'request_date' => now()->subDays(35)->toDateString(), 'required_date' => now()->subDays(28)->toDateString(), 'qty' => 3, 'size_ref' => 'S,M,L', 'submit_date' => now()->subDays(30)->toDateString(), 'approval_date' => now()->subDays(22)->toDateString(), 'status' => 'approved']);
        $washSample = Sample::create(['sample_no' => 'SMP-DEMOFULL-WASHSTD', 'style_id' => $style->id, 'buyer_id' => $buyer->id, 'sample_type_id' => $washStdType->id, 'merchandiser_id' => $merchandiser?->id, 'request_date' => now()->subDays(20)->toDateString(), 'submit_date' => now()->subDays(15)->toDateString(), 'approval_date' => now()->subDays(10)->toDateString(), 'status' => 'approved']);

        // --- BOM (buyer-provided PDF; no line items in this build) ---
        $bom = Bom::create(['bom_no' => 'BOM-DEMOFULL', 'style_id' => $style->id, 'version' => 1, 'status' => 'approved', 'approved_by' => $merchandiser?->id, 'approved_at' => now()->subDays(45), 'created_by' => $merchandiser?->id]);

        // --- Costing ---
        $costSheet = CostSheet::create(['cost_sheet_no' => 'CST-DEMOFULL', 'style_id' => $style->id, 'buyer_id' => $buyer->id, 'version' => 1, 'currency_id' => $currency->id, 'exchange_rate' => 1, 'order_qty' => 10000, 'smv' => 58.84, 'cm_minute_rate' => 0.037, 'efficiency_percent' => 72.5, 'commercial_percent' => 5, 'style_ref' => $style->style_no, 'fabric_cost' => 2.52 * 3.20 * 1.03, 'trims_cost' => 0.35, 'accessories_cost' => 0.10, 'print_emb_cost' => 0, 'wash_cost' => 0.18, 'commercial_cost' => 0.08, 'freight_cost' => 0.05, 'testing_cost' => 0.02, 'overhead_cost' => 0.12, 'profit_percent' => 12, 'price_type' => 'FOB', 'buyer_target_price' => 4.82, 'status' => 'approved', 'prepared_by' => $merchandiser?->id, 'approved_by' => $merchandiser?->id, 'approved_at' => now()->subDays(40)]);
        // Lines are per dozen: 2.52 yds/pc = 30.24 yds/dz.
        $costSheet->items()->create(['group' => 'fabric', 'item_id' => $fabricItem->id, 'description' => 'Main body fabric', 'consumption' => 2.52 * 12, 'uom_id' => $uomYard->id, 'rate' => 3.20]);
        $costSheet->recompute();

        // --- Sales Contract / PO ---
        $contract = SalesContract::create(['contract_no' => self::CONTRACT_NO, 'buyer_id' => $buyer->id, 'season_id' => $season->id, 'merchandiser_id' => $merchandiser?->id, 'factory_id' => $factory->id, 'inquiry_id' => $inquiry->id, 'buyer_order_ref' => 'CORVEX-PO-FULL', 'contract_date' => now()->subDays(30)->toDateString(), 'currency_id' => $currency->id, 'exchange_rate' => 1, 'delivery_term' => 'FOB', 'payment_term' => 'LC at sight', 'lc_no' => 'LC-DEMOFULL', 'lc_date' => now()->subDays(20)->toDateString(), 'lc_value' => 48200, 'lc_expiry' => now()->addDays(60)->toDateString(), 'status' => 'draft', 'created_by' => $merchandiser?->id]);

        $po = SalesContractPo::create(['sales_contract_id' => $contract->id, 'style_id' => $style->id, 'product_type_id' => $productType->id, 'color_id' => $color->id, 'wash_type_id' => $washType->id, 'po_no' => 'PO-FULL-0001', 'po_due_date' => now()->subDays(32)->toDateString(), 'po_qty' => 10000, 'unit_price' => 4.82, 'total_value' => 10000 * 4.82, 'price_type' => 'FOB', 'cost_smv' => 58.84, 'cm' => 3.00, 'fob_foc' => 4.82, 'pcd_date' => now()->addDays(5)->toDateString(), 'shipment_date' => now()->addDays(35)->toDateString(), 'ship_mode_id' => $shipMode->id, 'print_emb' => 'no', 'emb_applique_ih' => 'no', 'studs_stones_ih' => 'no', 'heat_seal_ih' => 'yes', 'status' => 'pending']);
        foreach ([$sizeS->id => 2500, $sizeM->id => 3000, $sizeL->id => 3000, $sizeXl->id => 1500] as $sizeId => $qty) {
            $po->sizes()->create(['size_id' => $sizeId, 'qty' => $qty]);
        }

        $contract->update(['status' => 'confirmed']);
        $contract->refreshTotals();

        $tnaPlan = app(TnaPlanGenerationService::class)->generateFor($po);
        $po->update(['status' => 'tna_created']);
        app(DocumentChecklistService::class)->generateFor($contract);

        app(PcdGateService::class)->evaluate($tnaPlan);
        app(TnaAlertService::class)->runDaily();

        app(SampleTnaSyncService::class)->syncFromSample($ppSample);
        app(SampleTnaSyncService::class)->syncFromSample($fitSample);
        app(SampleTnaSyncService::class)->syncFromSample($washSample);

        $fileHandoverTask = $tnaPlan->tasks()->where('task_code', 'file_handover')->first();
        $fileHandoverTask->update(['actual_date' => now()->subDays(3), 'status' => 'done']);
        foreach (['pullout', 'pp_meeting'] as $code) {
            $tnaPlan->tasks()->where('task_code', $code)->update(['actual_date' => now()->subDays(2), 'status' => 'done']);
        }

        // --- Sub-T&A: embroidery ---
        $subPlan = TnaSubPlan::create(['sub_no' => 'SUB-DEMOFULL', 'sales_contract_po_id' => $po->id, 'process_type' => 'embroidery', 'emb_print_type' => 'Chest logo, flat embroidery', 'required_psd' => now()->subDays(10)->toDateString(), 'required_pfd' => now()->addDays(2)->toDateString(), 'required_qty_per_day' => 800, 'plant_name' => 'Golden Needle Embroidery Ltd', 'po_qty' => 10000, 'status' => 'running']);
        $subPlan->logs()->create(['log_date' => now()->subDays(1)->toDateString(), 'sending_qty' => 2400, 'receiving_qty' => 2550]);

        // --- Material Booking (fabric + trims, fully received) ---
        $syncService = app(MaterialBookingTnaSyncService::class);

        $fabricBooking = MaterialBooking::create(['booking_no' => 'MB-DEMOFULL-FABRIC', 'type' => 'fabric', 'sales_contract_id' => $contract->id, 'sales_contract_po_id' => $po->id, 'style_id' => $style->id, 'supplier_id' => $fabricSupplier->id, 'mill_country' => 'China', 'booking_date' => now()->subDays(28)->toDateString(), 'pi_no' => 'PI-DEMOFULL', 'pi_date' => now()->subDays(28)->toDateString(), 'pi_value' => 25200 * 3.20, 'currency_id' => $currency->id, 'lc_no' => 'LC-DEMOFULL', 'lc_date' => now()->subDays(24)->toDateString(), 'lc_value' => 25200 * 3.20, 'lc_type' => 'LC', 'x_mill_date' => now()->subDays(18)->toDateString(), 'expected_inhouse_date' => now()->subDays(4)->toDateString(), 'status' => 'received']);
        $fabricBooking->items()->create(['item_id' => $fabricItem->id, 'color_id' => $color->id, 'booked_qty' => 25200, 'uom_id' => $uomYard->id, 'rate' => 3.20]);
        $syncService->syncFromBooking($fabricBooking);

        $consignment = $fabricBooking->consignments()->create(['consignment_no' => 1, 'planned_date' => now()->subDays(18)->toDateString(), 'planned_qty' => 25200]);
        $consignment->update(['actual_date' => now()->subDays(17)->toDateString(), 'received_qty' => 25200, 'challan_no' => 'CH-DEMOFULL-1', 'invoice_no' => 'INV-DEMOFULL-1', 'status' => 'received']);
        $syncService->syncFromConsignment($consignment);
        $fabricBooking->receipts()->create(['consignment_id' => $consignment->id, 'item_id' => $fabricItem->id, 'receive_date' => $consignment->actual_date, 'qty' => 25200, 'store_ref' => 'STORE-FULL-FAB', 'received_by' => $merchandiser?->id]);

        $trimsBooking = MaterialBooking::create(['booking_no' => 'MB-DEMOFULL-TRIMS', 'type' => 'trims', 'sales_contract_id' => $contract->id, 'sales_contract_po_id' => $po->id, 'style_id' => $style->id, 'supplier_id' => $trimsSupplier->id, 'booking_date' => now()->subDays(20)->toDateString(), 'expected_inhouse_date' => now()->subDays(5)->toDateString(), 'status' => 'received']);
        foreach (['THREAD', 'ZIPPER', 'MAIN_LABEL', 'SIZE_LABEL', 'CARE_LABEL', 'ELASTICS', 'BUTTONS', 'VELCRO'] as $code) {
            $trim = Item::where('code', $code)->firstOrFail();
            $trimsBooking->items()->create(['item_id' => $trim->id, 'booked_qty' => 10500, 'uom_id' => $trim->uom_id, 'rate' => $trim->default_price ?? 0.05]);
            $receipt = $trimsBooking->receipts()->create(['item_id' => $trim->id, 'receive_date' => now()->subDays(6)->toDateString(), 'qty' => 10500, 'store_ref' => "STORE-FULL-{$code}", 'received_by' => $merchandiser?->id]);
            $syncService->syncFromReceipt($receipt);
        }

        app(PcdGateService::class)->evaluate($tnaPlan->fresh());
        $tnaPlan->refresh();

        $style->update(['trc_product_id' => $trcProductId, 'trc_size_group_id' => $trcSizeGroupId]);

        $handover = app(ProductionHandoverService::class)->push($po->fresh(), $merchandiser?->id ?? 1);

        $line = \ME\MerchandisingTrace\Models\Bridge\TrcPlanLine::with('sizes')->find($handover->plan_line_id);
        $qtyPerSize = ['S' => 2500, 'M' => 3000, 'L' => 3000, 'XL' => 1500];
        foreach ($line->sizes as $lineSize) {
            $sizeName = Size::find($lineSize->size_id)?->name;
            $q = $qtyPerSize[$sizeName] ?? 0;
            $lineSize->update(['cut_qty' => $q, 'sewn_qty' => (int) ($q * 0.6), 'finished_qty' => (int) ($q * 0.3), 'passed_qty' => (int) ($q * 0.28), 'approved_qty' => (int) ($q * 0.28), 'packed_qty' => (int) ($q * 0.2), 'shipped_qty' => 0, 'reject_qty' => (int) ($q * 0.02)]);
        }
        app(ProductionProgressSyncService::class)->syncFor($po->fresh());

        $po->fresh()->shipmentBookings()->create(['planned_ship_date' => $po->effectiveShipment(), 'forwarder_name' => 'Maersk Line', 'booking_no' => 'BKG-DEMOFULL', 'vessel_flight' => 'MV Corvex Star / Voy 118E', 'is_short' => false, 'created_by' => $merchandiser?->id]);

        $documents = OrderDocument::where('sales_contract_id', $contract->id)->orderBy('due_date')->get();
        if ($documents->count() >= 2) {
            $documents[0]->update(['status' => 'uploaded', 'file_path' => 'demo/commercial-invoice.pdf', 'uploaded_at' => now()->subDays(2), 'uploaded_by' => $merchandiser?->id]);
            $documents[1]->update(['status' => 'approved', 'file_path' => 'demo/packing-list.pdf', 'uploaded_at' => now()->subDays(2), 'uploaded_by' => $merchandiser?->id]);
        }

        CommunicationLog::create(['style_id' => $style->id, 'sales_contract_po_id' => $po->id, 'log_date' => now()->subDays(24)->toDateString(), 'direction' => 'outbound', 'channel' => 'email', 'subject' => 'Order confirmation — full process', 'body' => 'Order confirmed, full Dev + Purchase pipeline in progress.', 'created_by' => $merchandiser?->id]);

        $this->command?->info('Demo order (full process) seeded: ' . self::CONTRACT_NO . ' — PCD ' . strtoupper($tnaPlan->fresh()->pcd_result) . ', handed over to production.');
    }
}
