<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\BuyerContact;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\CommunicationLog;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\Currency;
use ME\MerchandisingTrace\Models\ExchangeRate;
use ME\MerchandisingTrace\Models\Factory;
use ME\MerchandisingTrace\Models\FabricConsumption;
use ME\MerchandisingTrace\Models\Inquiry;
use ME\MerchandisingTrace\Models\InquiryItem;
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\ItemCategory;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\OrderDocument;
use ME\MerchandisingTrace\Models\ProductType;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\SampleType;
use ME\MerchandisingTrace\Models\Season;
use ME\MerchandisingTrace\Models\ShipMode;
use ME\MerchandisingTrace\Models\ShipmentBooking;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\StyleOperation;
use ME\MerchandisingTrace\Models\StylePart;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Models\TnaSubPlan;
use ME\MerchandisingTrace\Models\Uom;
use ME\MerchandisingTrace\Models\WashType;
use ME\MerchandisingTrace\Services\DocumentChecklistService;
use ME\MerchandisingTrace\Services\MaterialBookingTnaSyncService;
use ME\MerchandisingTrace\Services\PcdGateService;
use ME\MerchandisingTrace\Services\ProductionHandoverService;
use ME\MerchandisingTrace\Services\ProductionProgressSyncService;
use ME\MerchandisingTrace\Services\TnaAlertService;
use ME\MerchandisingTrace\Services\TnaPlanGenerationService;

/**
 * §13 Appendix C — the spec's own demo order (Buyer CORVEX, Merchant
 * Mr. Mizan, Fty DAL/DGL, Style RUE1 - Unisex Jacket - Black,
 * PO 4500396948, Qty 10,000), walked through the ENTIRE pipeline end to
 * end -- masters -> inquiry -> style (parts/embellishment/measurements) ->
 * sample -> BOM -> costing -> sales contract -> T&A (fail then pass) ->
 * sub-T&A -> material booking (fabric + trims) -> production handover
 * bridge -> reverse progress -> shipment -> documentation -> buyer
 * communication, so every module's tables carry at least one real row.
 *
 * Idempotent: re-running skips straight through if the demo contract
 * already exists, rather than creating a duplicate order.
 */
class DemoDataSeeder extends Seeder
{
    private const CONTRACT_NO = 'DEMO-CORVEX-4500396948';

    public function run(): void
    {
        if (SalesContract::where('contract_no', self::CONTRACT_NO)->exists()) {
            $this->command?->info('Demo order already exists — skipping.');

            return;
        }

        (new TrimsItemSeeder())->run();
        (new DefaultTnaTemplateSeeder())->run();
        (new DefaultDocumentTemplateSeeder())->run();
        (new SampleTypeSeeder())->run();

        $merchandiser = User::query()->first();

        // --- Masters (§13) ---
        $buyer = Buyer::firstOrCreate(['code' => 'CORVEX'], [
            'name' => 'CORVEX', 'merchandiser_id' => $merchandiser?->id, 'region' => 'North America',
            'agent_name' => 'PDS Agency', 'payment_term' => 'LC at sight', 'delivery_term' => 'FOB',
            'default_aql' => '2.5', 'is_active' => true,
        ]);
        BuyerContact::firstOrCreate(['buyer_id' => $buyer->id, 'email' => 'sourcing@corvex.example'], [
            'name' => 'Alex Morgan', 'designation' => 'Sourcing Manager', 'phone' => '+1-212-555-0134', 'is_primary' => true,
        ]);

        $season = Season::firstOrCreate(['code' => 'SS26'], ['name' => 'Spring/Summer 2026', 'year' => 2026, 'start_date' => '2026-01-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        $productType = ProductType::firstOrCreate(['code' => 'UJKT'], ['name' => 'Unisex Jacket', 'category' => 'Woven', 'default_smv' => 55, 'is_active' => true]);
        $color = Color::firstOrCreate(['code' => 'BLK'], ['name' => 'Black', 'is_active' => true]);
        $sizeS = Size::firstOrCreate(['name' => 'S'], ['sort_order' => 1, 'is_active' => true]);
        $sizeM = Size::firstOrCreate(['name' => 'M'], ['sort_order' => 2, 'is_active' => true]);
        $sizeL = Size::firstOrCreate(['name' => 'L'], ['sort_order' => 3, 'is_active' => true]);
        $sizeXl = Size::firstOrCreate(['name' => 'XL'], ['sort_order' => 4, 'is_active' => true]);
        $washType = WashType::firstOrCreate(['code' => 'ENZ'], ['name' => 'Enzyme Wash', 'is_active' => true]);
        $shipMode = ShipMode::firstOrCreate(['code' => 'SEA'], ['name' => 'SEA', 'is_active' => true]);
        $factory = Factory::firstOrCreate(['code' => 'DALDGL'], [
            'name' => 'DAL/DGL', 'address' => 'Dhaka EPZ', 'unit_type' => 'Woven', 'capacity_per_month' => 250000, 'is_own' => true, 'is_active' => true,
        ]);
        $fabricSupplier = Supplier::firstOrCreate(['code' => 'CNFAB01'], [
            'name' => 'China Fabric Mills Ltd', 'type' => 'fabric_mill', 'country' => 'China', 'contact' => 'export@cnfabric.example',
            'lead_time_days' => 45, 'payment_term' => 'TT', 'rating' => 4, 'is_active' => true,
        ]);
        $trimsSupplier = Supplier::firstOrCreate(['code' => 'BDTRIM01'], [
            'name' => 'BD Trims & Accessories', 'type' => 'trims', 'country' => 'Bangladesh', 'contact' => 'sales@bdtrims.example',
            'lead_time_days' => 15, 'payment_term' => 'Cash', 'rating' => 5, 'is_active' => true,
        ]);
        $currency = Currency::firstOrCreate(['code' => 'USD'], ['name' => 'US Dollar', 'symbol' => '$', 'is_active' => true]);
        ExchangeRate::firstOrCreate(['currency_id' => $currency->id, 'effective_date' => now()->toDateString()], ['rate' => 1.0]);
        $fabricCategory = ItemCategory::firstOrCreate(['code' => 'FABRIC'], ['name' => 'Fabric', 'is_active' => true]);
        $uomYard = Uom::firstOrCreate(['code' => 'YDS'], ['name' => 'Yards', 'decimal_places' => 2, 'is_active' => true]);
        $fabricItem = Item::firstOrCreate(['code' => 'MAINFAB-RUE1'], [
            'name' => 'RUE1 Main Fabric — Twill 60/40', 'category_id' => $fabricCategory->id, 'type' => 'fabric',
            'uom_id' => $uomYard->id, 'default_supplier_id' => $fabricSupplier->id, 'default_price' => 3.20,
            'consumption_uom' => 'yds', 'is_active' => true,
        ]);

        // --- Inquiry -> Style (§M02/§M03) ---
        $inquiry = Inquiry::create([
            'inquiry_no' => 'INQ-DEMO-' . now()->format('ymd'),
            'inquiry_given_date' => now()->subDays(60)->toDateString(),
            'buyer_id' => $buyer->id, 'season_id' => $season->id, 'merchandiser_id' => $merchandiser?->id,
            'factory_id' => $factory->id,
            'order_confirmation_due_date' => now()->subDays(30)->toDateString(),
            'product_type_id' => $productType->id,
            'description' => 'Unisex jacket, enzyme wash, heat-seal branding',
            'target_qty' => 10000, 'target_price' => 4.82, 'target_ship_date' => now()->addDays(90)->toDateString(),
            'status' => 'quoted',
        ]);
        InquiryItem::create([
            'inquiry_id' => $inquiry->id, 'style_ref' => 'RUE1', 'product_type_id' => $productType->id,
            'color_ref' => 'Black', 'qty' => 10000, 'target_price' => 4.82,
        ]);

        $trcProductId = DB::table('trc_products')->where('code', 'UJKT')->value('id')
            ?? DB::table('trc_products')->insertGetId(['code' => 'UJKT', 'name' => 'Unisex Jacket', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $trcSizeGroupId = DB::table('trc_size_groups')->where('name', 'S-M-L-XL')->value('id')
            ?? DB::table('trc_size_groups')->insertGetId(['name' => 'S-M-L-XL', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $trcPartId = DB::table('trc_parts')->where('code', 'CHEST')->value('id')
            ?? DB::table('trc_parts')->insertGetId(['code' => 'CHEST', 'name' => 'Chest Panel', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);

        $style = Style::create([
            'style_no' => 'RUE1', 'name' => 'RUE1 - Unisex Jacket - Black',
            'description' => 'Unisex jacket with heat-seal chest branding', 'buyer_id' => $buyer->id,
            'inquiry_id' => $inquiry->id, 'season_id' => $season->id, 'merchandiser_id' => $merchandiser?->id,
            'wash_type_id' => $washType->id, 'product_type_id' => $productType->id, 'trc_product_id' => $trcProductId,
            'trc_size_group_id' => $trcSizeGroupId, 'smv' => 55.00, 'cost_smv' => 58.84, 'target_cm' => 3.00,
            'fabric_description' => 'Twill 60% cotton / 40% polyester, 240 GSM', 'development_status' => 'sample_stage',
            'is_active' => true, 'created_by' => $merchandiser?->id,
        ]);
        $inquiry->update(['status' => 'confirmed']);

        $style->images()->create(['path' => 'demo/rue1-front.jpg', 'type' => 'front', 'caption' => 'RUE1 front view']);
        $style->images()->create(['path' => 'demo/rue1-embellishment.jpg', 'type' => 'embellishment', 'caption' => 'Heat-seal chest logo placement']);

        $chest = $style->measurements()->create(['pom_code' => 'CH', 'pom_name' => 'Chest Width', 'tolerance_plus' => 0.5, 'tolerance_minus' => 0.5, 'sort_order' => 1]);
        $chest->sizes()->create(['size_id' => $sizeS->id, 'value' => 21.0]);
        $chest->sizes()->create(['size_id' => $sizeM->id, 'value' => 22.0]);
        $chest->sizes()->create(['size_id' => $sizeL->id, 'value' => 23.0]);
        $chest->sizes()->create(['size_id' => $sizeXl->id, 'value' => 24.0]);
        $length = $style->measurements()->create(['pom_code' => 'LEN', 'pom_name' => 'Body Length', 'tolerance_plus' => 0.375, 'tolerance_minus' => 0.375, 'sort_order' => 2]);
        $length->sizes()->create(['size_id' => $sizeS->id, 'value' => 27.0]);
        $length->sizes()->create(['size_id' => $sizeM->id, 'value' => 27.75]);
        $length->sizes()->create(['size_id' => $sizeL->id, 'value' => 28.5]);
        $length->sizes()->create(['size_id' => $sizeXl->id, 'value' => 29.25]);

        $part = StylePart::create([
            'style_id' => $style->id, 'trc_part_id' => $trcPartId, 'qty_per_garment' => 1,
            'embellishment_type' => 'heat_seal', 'placement' => 'Left chest', 'is_critical' => true,
        ]);
        $style->operations()->create(['operation_name' => 'Chest panel heat-seal application', 'machine_type' => 'Heat Press', 'smv' => 0.65, 'sequence' => 1]);
        $style->operations()->create(['operation_name' => 'Sleeve set', 'machine_type' => 'Overlock', 'smv' => 1.20, 'sequence' => 2]);

        FabricConsumption::create([
            'style_id' => $style->id, 'color_id' => $color->id, 'item_id' => $fabricItem->id,
            'yy' => 2.52, 'marker_efficiency' => 82.5, 'gsm' => 240, 'width' => 58, 'calculated_by' => $merchandiser?->id, 'method' => 'marker',
        ]);

        // --- Sample (§M04) ---
        $ppType = SampleType::where('code', 'PP1')->firstOrFail();
        $fitType = SampleType::where('code', 'FIT')->firstOrFail();

        $fitSample = Sample::create([
            'sample_no' => 'SMP-DEMO-FIT', 'style_id' => $style->id, 'buyer_id' => $buyer->id,
            'sample_type_id' => $fitType->id, 'merchandiser_id' => $merchandiser?->id,
            'request_date' => now()->subDays(55)->toDateString(), 'required_date' => now()->subDays(50)->toDateString(),
            'qty' => 2, 'size_ref' => 'M', 'submit_date' => now()->subDays(52)->toDateString(),
            'approval_date' => now()->subDays(48)->toDateString(), 'status' => 'approved',
            'buyer_comments' => 'Fit looks good, proceed to PP.',
        ]);
        $fitSample->comments()->create(['comment' => 'Chest a touch tight on M, monitor at PP.', 'commented_by' => $merchandiser?->id, 'comment_date' => now()->subDays(53), 'is_buyer_comment' => false]);

        $ppSample = Sample::create([
            'sample_no' => 'SMP-DEMO-PP1', 'style_id' => $style->id, 'buyer_id' => $buyer->id,
            'sample_type_id' => $ppType->id, 'merchandiser_id' => $merchandiser?->id,
            'request_date' => now()->subDays(35)->toDateString(), 'required_date' => now()->subDays(28)->toDateString(),
            'qty' => 3, 'size_ref' => 'S,M,L', 'submit_date' => now()->subDays(30)->toDateString(),
            'courier_name' => 'DHL', 'tracking_no' => 'DHL-DEMO-001',
            'approval_date' => now()->subDays(22)->toDateString(), 'status' => 'approved',
            'buyer_comments' => 'Approved for bulk.',
        ]);
        $ppSample->comments()->create(['comment' => 'Buyer approved PP with no comments.', 'commented_by' => $merchandiser?->id, 'comment_date' => now()->subDays(22), 'is_buyer_comment' => true]);

        // --- BOM (§M05) — buyer-provided PDF; no line items in this build ---
        $bom = Bom::create(['bom_no' => 'BOM-DEMO-RUE1', 'style_id' => $style->id, 'version' => 1, 'status' => 'approved', 'approved_by' => $merchandiser?->id, 'approved_at' => now()->subDays(45), 'created_by' => $merchandiser?->id]);

        // --- Costing (§M06) — CM = (58.84/72.5%)*0.037 ≈ 3.00, matching §13's own CM 3.00 ---
        $costSheet = CostSheet::create([
            'cost_sheet_no' => 'CST-DEMO-RUE1', 'style_id' => $style->id, 'buyer_id' => $buyer->id, 'version' => 1,
            'currency_id' => $currency->id, 'exchange_rate' => 1, 'order_qty' => 10000,
            'smv' => 58.84, 'cm_minute_rate' => 0.037, 'efficiency_percent' => 72.5,
            'fabric_cost' => 2.52 * 3.20 * 1.03, 'trims_cost' => 0.35, 'accessories_cost' => 0.10,
            'print_emb_cost' => 0, 'wash_cost' => 0.18, 'commercial_cost' => 0.08, 'freight_cost' => 0.05,
            'testing_cost' => 0.02, 'overhead_cost' => 0.12, 'profit_percent' => 12, 'price_type' => 'FOB',
            'buyer_target_price' => 4.82, 'status' => 'approved', 'prepared_by' => $merchandiser?->id,
            'approved_by' => $merchandiser?->id, 'approved_at' => now()->subDays(40),
        ]);
        $costSheet->recompute();
        $costSheet->items()->create(['group' => 'fabric', 'item_id' => $fabricItem->id, 'description' => 'Main body fabric', 'consumption' => 2.52, 'uom_id' => $uomYard->id, 'rate' => 3.20]);
        $costSheet->items()->create(['group' => 'process', 'description' => 'CM (sewing)', 'consumption' => 1, 'rate' => $costSheet->calcCm()]);

        // --- Sales Contract / Order Confirmation (§M07) ---
        $contract = SalesContract::create([
            'contract_no' => self::CONTRACT_NO, 'buyer_id' => $buyer->id, 'season_id' => $season->id,
            'merchandiser_id' => $merchandiser?->id, 'factory_id' => $factory->id, 'inquiry_id' => $inquiry->id,
            'buyer_order_ref' => 'CORVEX-PO-4500396948', 'contract_date' => now()->subDays(30)->toDateString(),
            'currency_id' => $currency->id, 'exchange_rate' => 1, 'delivery_term' => 'FOB', 'payment_term' => 'LC at sight',
            'lc_no' => 'LC-DEMO-9981', 'lc_date' => now()->subDays(20)->toDateString(), 'lc_value' => 48200,
            'lc_expiry' => now()->addDays(60)->toDateString(), 'status' => 'draft', 'created_by' => $merchandiser?->id,
        ]);

        $po = SalesContractPo::create([
            'sales_contract_id' => $contract->id, 'style_id' => $style->id, 'product_type_id' => $productType->id,
            'color_id' => $color->id, 'wash_type_id' => $washType->id, 'po_no' => '4500396948',
            'po_due_date' => now()->subDays(32)->toDateString(), 'po_qty' => 9800, 'unit_price' => 4.82,
            'total_value' => 9800 * 4.82, 'price_type' => 'FOB', 'cost_smv' => 58.84, 'cm' => 3.00, 'fob_foc' => 4.82,
            'pcd_date' => now()->addDays(5)->toDateString(), 'shipment_date' => now()->addDays(35)->toDateString(),
            'ship_mode_id' => $shipMode->id, 'print_emb' => 'no', 'emb_applique_ih' => 'no',
            'studs_stones_ih' => 'no', 'heat_seal_ih' => 'yes', 'status' => 'pending',
        ]);
        foreach ([$sizeS->id => 2000, $sizeM->id => 3000, $sizeL->id => 3000, $sizeXl->id => 1800] as $sizeId => $qty) {
            $po->sizes()->create(['size_id' => $sizeId, 'qty' => $qty]);
        }

        // §6 Rule 2 demo: buyer increases the qty by 200 with a logged reason.
        $po->update(['po_qty_revised_1' => 10000]);
        $po->revisions()->create([
            'field' => 'po_qty', 'old_value' => 9800, 'new_value' => 10000,
            'reason' => 'Buyer added 200 pcs to top up a container.', 'changed_by' => $merchandiser?->id, 'changed_at' => now()->subDays(25),
        ]);
        $po->sizes()->where('size_id', $sizeXl->id)->update(['qty' => 2000]);

        $contract->update(['status' => 'confirmed']);
        $contract->refreshTotals();

        $tnaPlan = app(TnaPlanGenerationService::class)->generateFor($po);
        $po->update(['status' => 'tna_created']);
        app(DocumentChecklistService::class)->generateFor($contract);

        // --- T&A (§M08) — demonstrate the FAIL path first, matching §13's
        // own demo narrative ("Due to bulk fabric not received"), then
        // clear it so the order can proceed all the way to production.
        app(PcdGateService::class)->evaluate($tnaPlan);
        $tnaPlan->refresh();
        $tnaPlan->update(['pcd_fail_reason' => 'Due to bulk fabric not received']);

        app(TnaAlertService::class)->runDaily();

        // Sample/PP auto-sync (the tasks were generated before the samples
        // above existed on this style — re-run the sync now that both do).
        app(\ME\MerchandisingTrace\Services\SampleTnaSyncService::class)->syncFromSample($ppSample);
        app(\ME\MerchandisingTrace\Services\SampleTnaSyncService::class)->syncFromSample($fitSample);

        // A manual (non-auto) task edited by hand, with the mandatory log entry.
        $fileHandoverTask = $tnaPlan->tasks()->where('task_code', 'file_handover')->first();
        $fileHandoverTask->logs()->create([
            'field' => 'actual_date', 'old_value' => null, 'new_value' => now()->subDays(3)->toDateString(),
            'changed_by' => $merchandiser?->id, 'changed_at' => now()->subDays(3), 'reason' => 'File handed to production floor.',
        ]);
        $fileHandoverTask->update(['actual_date' => now()->subDays(3), 'status' => 'done']);

        foreach (['pullout', 'pp_meeting'] as $code) {
            $tnaPlan->tasks()->where('task_code', $code)->update(['actual_date' => now()->subDays(2), 'status' => 'done']);
        }

        // --- Sub-T&A: embroidery (§M09) ---
        $subPlan = TnaSubPlan::create([
            'sub_no' => 'SUB-DEMO-EMB', 'sales_contract_po_id' => $po->id, 'process_type' => 'embroidery',
            'emb_print_type' => 'Chest logo, flat embroidery', 'required_psd' => now()->subDays(10)->toDateString(),
            'required_pfd' => now()->addDays(2)->toDateString(), 'required_qty_per_day' => 800,
            'plant_name' => 'Golden Needle Embroidery Ltd', 'po_qty' => 10000, 'status' => 'running',
        ]);
        $subPlan->logs()->create(['log_date' => now()->subDays(3)->toDateString(), 'sending_qty' => 2500, 'receiving_qty' => 2100]);
        $subPlan->logs()->create(['log_date' => now()->subDays(2)->toDateString(), 'sending_qty' => 2600, 'receiving_qty' => 2400]);
        $subPlan->logs()->create(['log_date' => now()->subDays(1)->toDateString(), 'sending_qty' => 2400, 'receiving_qty' => 2550]);

        // Wash Standard sample -- the other blocking sample-status task.
        $washStdType = SampleType::where('code', 'WASHSTD')->firstOrFail();
        $washSample = Sample::create([
            'sample_no' => 'SMP-DEMO-WASHSTD', 'style_id' => $style->id, 'buyer_id' => $buyer->id,
            'sample_type_id' => $washStdType->id, 'merchandiser_id' => $merchandiser?->id,
            'request_date' => now()->subDays(20)->toDateString(), 'submit_date' => now()->subDays(15)->toDateString(),
            'approval_date' => now()->subDays(10)->toDateString(), 'status' => 'approved',
        ]);
        app(\ME\MerchandisingTrace\Services\SampleTnaSyncService::class)->syncFromSample($washSample);

        // --- Material Booking (§M10) ---
        $syncService = app(MaterialBookingTnaSyncService::class);

        $fabricBooking = MaterialBooking::create([
            'booking_no' => 'MB-DEMO-FABRIC', 'type' => 'fabric', 'sales_contract_id' => $contract->id,
            'sales_contract_po_id' => $po->id, 'style_id' => $style->id, 'supplier_id' => $fabricSupplier->id,
            'mill_country' => 'China', 'booking_date' => now()->subDays(28)->toDateString(), 'pi_no' => 'PI-DEMO-8871',
            'pi_date' => now()->subDays(28)->toDateString(), 'pi_value' => 25200 * 3.20, 'currency_id' => $currency->id,
            'lc_no' => 'LC-DEMO-9981', 'lc_date' => now()->subDays(24)->toDateString(), 'lc_value' => 25200 * 3.20,
            'lc_type' => 'LC', 'x_mill_date' => now()->subDays(18)->toDateString(),
            'expected_inhouse_date' => now()->subDays(4)->toDateString(), 'status' => 'received',
        ]);
        $fabricBooking->items()->create(['item_id' => $fabricItem->id, 'color_id' => $color->id, 'booked_qty' => 25200, 'uom_id' => $uomYard->id, 'rate' => 3.20]);
        $syncService->syncFromBooking($fabricBooking);

        $consignmentPlan = [1 => 8000, 2 => 8000, 3 => 6000, 4 => 3200];
        foreach ($consignmentPlan as $no => $qty) {
            $consignment = $fabricBooking->consignments()->create([
                'consignment_no' => $no, 'planned_date' => now()->subDays(20 - $no * 2)->toDateString(),
                'planned_qty' => $qty,
            ]);
            // All 4 consignments received -- needed for the fabric_in_house
            // checklist item to pass and reach production handover below.
            $consignment->update([
                'actual_date' => now()->subDays(19 - $no * 2)->toDateString(), 'received_qty' => $qty,
                'challan_no' => "CH-DEMO-{$no}", 'invoice_no' => "INV-DEMO-{$no}", 'status' => 'received',
            ]);
            $syncService->syncFromConsignment($consignment);
            $fabricBooking->receipts()->create([
                'consignment_id' => $consignment->id, 'item_id' => $fabricItem->id,
                'receive_date' => $consignment->actual_date, 'qty' => $qty, 'store_ref' => "STORE-FAB-{$no}",
                'received_by' => $merchandiser?->id,
            ]);
        }

        $trimsBooking = MaterialBooking::create([
            'booking_no' => 'MB-DEMO-TRIMS', 'type' => 'trims', 'sales_contract_id' => $contract->id,
            'sales_contract_po_id' => $po->id, 'style_id' => $style->id, 'supplier_id' => $trimsSupplier->id,
            'booking_date' => now()->subDays(20)->toDateString(), 'expected_inhouse_date' => now()->subDays(5)->toDateString(),
            'status' => 'received',
        ]);
        foreach (['THREAD', 'ZIPPER', 'MAIN_LABEL', 'SIZE_LABEL', 'CARE_LABEL', 'ELASTICS', 'BUTTONS', 'VELCRO'] as $code) {
            $trim = Item::where('code', $code)->firstOrFail();
            $trimsBooking->items()->create(['item_id' => $trim->id, 'booked_qty' => 10500, 'uom_id' => $trim->uom_id, 'rate' => $trim->default_price ?? 0.05]);
            $receipt = $trimsBooking->receipts()->create([
                'item_id' => $trim->id, 'receive_date' => now()->subDays(6)->toDateString(), 'qty' => 10500,
                'store_ref' => "STORE-TRIM-{$code}", 'received_by' => $merchandiser?->id,
            ]);
            $syncService->syncFromReceipt($receipt);
        }

        // Every blocking task is now done -> PCD re-evaluates to PASS.
        app(PcdGateService::class)->evaluate($tnaPlan->fresh());
        $tnaPlan->refresh();

        // --- Style parts mapping (one-time, then handover is automatic) ---
        $style->update(['trc_product_id' => $trcProductId, 'trc_size_group_id' => $trcSizeGroupId]);

        // --- Production Handover Bridge (§M11) ---
        $handover = app(ProductionHandoverService::class)->push($po->fresh(), $merchandiser?->id ?? 1);

        // --- Reverse progress feed (§M11) ---
        $line = \ME\MerchandisingTrace\Models\Bridge\TrcPlanLine::with('sizes')->find($handover->plan_line_id);
        $qtyPerSize = ['S' => 2000, 'M' => 3000, 'L' => 3000, 'XL' => 2000];
        foreach ($line->sizes as $lineSize) {
            $sizeName = Size::find($lineSize->size_id)?->name;
            $q = $qtyPerSize[$sizeName] ?? 0;
            $lineSize->update([
                'cut_qty' => $q, 'sewn_qty' => (int) ($q * 0.7), 'finished_qty' => (int) ($q * 0.4),
                'passed_qty' => (int) ($q * 0.38), 'approved_qty' => (int) ($q * 0.38),
                'packed_qty' => (int) ($q * 0.3), 'shipped_qty' => 0, 'reject_qty' => (int) ($q * 0.02),
            ]);
        }
        app(ProductionProgressSyncService::class)->syncFor($po->fresh());

        // --- Shipment Plan (§M12) ---
        $po->fresh()->shipmentBookings()->create([
            'planned_ship_date' => $po->effectiveShipment(), 'forwarder_name' => 'Maersk Line',
            'booking_no' => 'BKG-DEMO-5521', 'vessel_flight' => 'MV Corvex Star / Voy 118E',
            'is_short' => false, 'created_by' => $merchandiser?->id,
        ]);

        // --- Documentation (§M13): mark the earliest-due docs as progressing ---
        $documents = OrderDocument::where('sales_contract_id', $contract->id)->orderBy('due_date')->get();
        if ($documents->count() >= 2) {
            $documents[0]->update(['status' => 'uploaded', 'file_path' => 'demo/commercial-invoice.pdf', 'uploaded_at' => now()->subDays(2), 'uploaded_by' => $merchandiser?->id]);
            $documents[1]->update(['status' => 'approved', 'file_path' => 'demo/packing-list.pdf', 'uploaded_at' => now()->subDays(2), 'uploaded_by' => $merchandiser?->id]);
        }

        // --- Buyer Communication (§M14) ---
        CommunicationLog::create([
            'style_id' => $style->id, 'sales_contract_po_id' => $po->id, 'log_date' => now()->subDays(25)->toDateString(),
            'direction' => 'inbound', 'channel' => 'email', 'subject' => 'PP sample approval + qty top-up',
            'body' => "Approved the PP sample. Please add 200 pcs to top up the container to a full 10,000.",
            'created_by' => $merchandiser?->id,
        ]);
        CommunicationLog::create([
            'style_id' => $style->id, 'sales_contract_po_id' => $po->id, 'log_date' => now()->subDays(24)->toDateString(),
            'direction' => 'outbound', 'channel' => 'email', 'subject' => 'RE: PP sample approval + qty top-up',
            'body' => 'Confirmed, PO revised to 10,000 pcs. Fabric booking in progress.',
            'follow_up_date' => now()->addDays(5)->toDateString(), 'created_by' => $merchandiser?->id,
        ]);

        $this->command?->info('Demo order seeded: ' . self::CONTRACT_NO . ' / PO ' . $po->po_no . ' — PCD ' . strtoupper($tnaPlan->pcd_result) . ', handed over to production (plan line #' . $handover->plan_line_id . ').');
    }
}
