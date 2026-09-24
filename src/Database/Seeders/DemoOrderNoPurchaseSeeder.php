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
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\SampleType;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\StylePart;
use ME\MerchandisingTrace\Services\DocumentChecklistService;
use ME\MerchandisingTrace\Services\PcdGateService;
use ME\MerchandisingTrace\Services\ProductionHandoverService;
use ME\MerchandisingTrace\Services\ProductionProgressSyncService;
use ME\MerchandisingTrace\Services\SampleTnaSyncService;
use ME\MerchandisingTrace\Services\TnaPlanGenerationService;

/**
 * Demo order type 3 — "Purchase kora lagbe na": buyer supplies fabric and
 * trims directly (style flagged fabric_sourced_by=buyer), so NO Material
 * Booking rows are ever created for it. TnaPlanGenerationService
 * pre-marks every material-booking-sourced T&A task n/a for such styles,
 * and PreFlightChecklistService skips the fabric/trims-in-house checks
 * the same way — so PCD reaches PASS from Dev + Pilot tasks alone.
 * Dev/Sample stage still runs in full (requires_dev_sample = true).
 */
class DemoOrderNoPurchaseSeeder extends Seeder
{
    use SeedsDemoMasters;

    private const CONTRACT_NO = 'DEMO-CORVEX-NOPUR';
    private const STYLE_NO = 'RUE-NOPUR';

    public function run(): void
    {
        if (SalesContract::where('contract_no', self::CONTRACT_NO)->exists()) {
            $this->command?->info('Demo order (no purchase) already exists — skipping.');

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
            'inquiry_no' => 'INQ-DEMO-NOPUR-' . now()->format('ymd'),
            'inquiry_given_date' => now()->subDays(45)->toDateString(),
            'buyer_id' => $buyer->id, 'season_id' => $season->id, 'merchandiser_id' => $merchandiser?->id,
            'factory_id' => $factory->id, 'order_confirmation_due_date' => now()->subDays(25)->toDateString(),
            'product_type_id' => $productType->id, 'description' => 'CMT order — buyer supplies fabric & trims directly',
            'style_ref' => self::STYLE_NO, 'color_ref' => 'Black',
            'target_qty' => 6000, 'target_price' => 3.10, 'target_ship_date' => now()->addDays(60)->toDateString(),
            'status' => 'confirmed',
        ]);

        $style = Style::create([
            'style_no' => self::STYLE_NO, 'name' => self::STYLE_NO . ' - Unisex Jacket - Black (CMT)',
            'description' => 'CMT order — buyer supplies fabric & trims directly, no procurement tracked here',
            'buyer_id' => $buyer->id, 'inquiry_id' => $inquiry->id, 'season_id' => $season->id,
            'merchandiser_id' => $merchandiser?->id, 'wash_type_id' => $washType->id, 'product_type_id' => $productType->id,
            'trc_product_id' => $trcProductId, 'trc_size_group_id' => $trcSizeGroupId,
            'smv' => 55.00, 'cost_smv' => 58.84, 'target_cm' => 3.00,
            'fabric_description' => 'Buyer-supplied twill 60% cotton / 40% polyester, 240 GSM', 'development_status' => 'sample_stage',
            'requires_dev_sample' => true, 'fabric_sourced_by' => 'buyer',
            'is_active' => true, 'created_by' => $merchandiser?->id,
        ]);

        $style->images()->create(['path' => 'demo/rue-nopur-front.jpg', 'type' => 'front', 'caption' => 'Front view']);
        $chest = $style->measurements()->create(['pom_code' => 'CH', 'pom_name' => 'Chest Width', 'tolerance_plus' => 0.5, 'tolerance_minus' => 0.5, 'sort_order' => 1]);
        $chest->sizes()->create(['size_id' => $sizeS->id, 'value' => 21.0]);
        $chest->sizes()->create(['size_id' => $sizeM->id, 'value' => 22.0]);
        $chest->sizes()->create(['size_id' => $sizeL->id, 'value' => 23.0]);
        $chest->sizes()->create(['size_id' => $sizeXl->id, 'value' => 24.0]);

        StylePart::create(['style_id' => $style->id, 'trc_part_id' => $trcPartId, 'qty_per_garment' => 1, 'embellishment_type' => 'heat_seal', 'placement' => 'Left chest', 'is_critical' => true]);
        $style->operations()->create(['operation_name' => 'Sleeve set', 'machine_type' => 'Overlock', 'smv' => 1.20, 'sequence' => 1]);

        FabricConsumption::create(['style_id' => $style->id, 'color_id' => $color->id, 'item_id' => $fabricItem->id, 'yy' => 2.52, 'marker_efficiency' => 82.5, 'gsm' => 240, 'width' => 58, 'calculated_by' => $merchandiser?->id, 'method' => 'marker']);

        // --- Dev: samples still run (requires_dev_sample = true) ---
        $ppType = SampleType::where('code', 'PP1')->firstOrFail();
        $fitType = SampleType::where('code', 'FIT')->firstOrFail();
        $washStdType = SampleType::where('code', 'WASHSTD')->firstOrFail();

        $fitSample = Sample::create(['sample_no' => 'SMP-DEMONOPUR-FIT', 'style_id' => $style->id, 'buyer_id' => $buyer->id, 'sample_type_id' => $fitType->id, 'merchandiser_id' => $merchandiser?->id, 'request_date' => now()->subDays(40)->toDateString(), 'required_date' => now()->subDays(36)->toDateString(), 'qty' => 2, 'size_ref' => 'M', 'submit_date' => now()->subDays(38)->toDateString(), 'approval_date' => now()->subDays(34)->toDateString(), 'status' => 'approved']);
        $ppSample = Sample::create(['sample_no' => 'SMP-DEMONOPUR-PP1', 'style_id' => $style->id, 'buyer_id' => $buyer->id, 'sample_type_id' => $ppType->id, 'merchandiser_id' => $merchandiser?->id, 'request_date' => now()->subDays(25)->toDateString(), 'required_date' => now()->subDays(20)->toDateString(), 'qty' => 3, 'size_ref' => 'S,M,L', 'submit_date' => now()->subDays(22)->toDateString(), 'approval_date' => now()->subDays(15)->toDateString(), 'status' => 'approved']);
        $washSample = Sample::create(['sample_no' => 'SMP-DEMONOPUR-WASHSTD', 'style_id' => $style->id, 'buyer_id' => $buyer->id, 'sample_type_id' => $washStdType->id, 'merchandiser_id' => $merchandiser?->id, 'request_date' => now()->subDays(14)->toDateString(), 'submit_date' => now()->subDays(10)->toDateString(), 'approval_date' => now()->subDays(6)->toDateString(), 'status' => 'approved']);

        // --- BOM (buyer-provided PDF; no line items in this build) ---
        $bom = Bom::create(['bom_no' => 'BOM-DEMONOPUR', 'style_id' => $style->id, 'version' => 1, 'status' => 'approved', 'approved_by' => $merchandiser?->id, 'approved_at' => now()->subDays(20), 'created_by' => $merchandiser?->id]);

        // --- Costing (CMT rate only — no fabric/trims cost since buyer supplies) ---
        $costSheet = CostSheet::create(['cost_sheet_no' => 'CST-DEMONOPUR', 'style_id' => $style->id, 'buyer_id' => $buyer->id, 'version' => 1, 'currency_id' => $currency->id, 'exchange_rate' => 1, 'order_qty' => 6000, 'smv' => 58.84, 'cm_minute_rate' => 0.037, 'efficiency_percent' => 72.5, 'commercial_percent' => 5, 'style_ref' => $style->style_no, 'fabric_cost' => 0, 'trims_cost' => 0, 'accessories_cost' => 0, 'print_emb_cost' => 0, 'wash_cost' => 0, 'commercial_cost' => 0.05, 'freight_cost' => 0, 'testing_cost' => 0.02, 'overhead_cost' => 0.10, 'profit_percent' => 15, 'price_type' => 'CMT', 'buyer_target_price' => 3.10, 'status' => 'approved', 'prepared_by' => $merchandiser?->id, 'approved_by' => $merchandiser?->id, 'approved_at' => now()->subDays(18)]);
        $costSheet->recompute();

        // --- Sales Contract / PO ---
        $contract = SalesContract::create(['contract_no' => self::CONTRACT_NO, 'buyer_id' => $buyer->id, 'season_id' => $season->id, 'merchandiser_id' => $merchandiser?->id, 'factory_id' => $factory->id, 'inquiry_id' => $inquiry->id, 'buyer_order_ref' => 'CORVEX-PO-NOPUR', 'contract_date' => now()->subDays(25)->toDateString(), 'currency_id' => $currency->id, 'exchange_rate' => 1, 'delivery_term' => 'CMT', 'payment_term' => 'TT 30 days', 'status' => 'draft', 'created_by' => $merchandiser?->id]);

        $po = SalesContractPo::create(['sales_contract_id' => $contract->id, 'style_id' => $style->id, 'product_type_id' => $productType->id, 'color_id' => $color->id, 'wash_type_id' => $washType->id, 'po_no' => 'PO-NOPUR-0001', 'po_due_date' => now()->subDays(22)->toDateString(), 'po_qty' => 6000, 'unit_price' => 3.10, 'total_value' => 6000 * 3.10, 'price_type' => 'CMT', 'cost_smv' => 58.84, 'cm' => 3.00, 'fob_foc' => 3.10, 'pcd_date' => now()->addDays(2)->toDateString(), 'shipment_date' => now()->addDays(25)->toDateString(), 'ship_mode_id' => $shipMode->id, 'print_emb' => 'no', 'emb_applique_ih' => 'no', 'studs_stones_ih' => 'no', 'heat_seal_ih' => 'yes', 'status' => 'pending']);
        foreach ([$sizeS->id => 1500, $sizeM->id => 2000, $sizeL->id => 1700, $sizeXl->id => 800] as $sizeId => $qty) {
            $po->sizes()->create(['size_id' => $sizeId, 'qty' => $qty]);
        }

        $contract->update(['status' => 'confirmed']);
        $contract->refreshTotals();

        $tnaPlan = app(TnaPlanGenerationService::class)->generateFor($po);
        $po->update(['status' => 'tna_created']);
        app(DocumentChecklistService::class)->generateFor($contract);

        app(SampleTnaSyncService::class)->syncFromSample($ppSample);
        app(SampleTnaSyncService::class)->syncFromSample($fitSample);
        app(SampleTnaSyncService::class)->syncFromSample($washSample);

        $fileHandoverTask = $tnaPlan->tasks()->where('task_code', 'file_handover')->first();
        $fileHandoverTask->update(['actual_date' => now()->subDays(2), 'status' => 'done']);
        foreach (['pullout', 'pp_meeting'] as $code) {
            $tnaPlan->tasks()->where('task_code', $code)->update(['actual_date' => now()->subDays(1), 'status' => 'done']);
        }

        // --- No Material Booking created (fabric_sourced_by = buyer) ---

        app(PcdGateService::class)->evaluate($tnaPlan->fresh());
        $tnaPlan->refresh();

        $style->update(['trc_product_id' => $trcProductId, 'trc_size_group_id' => $trcSizeGroupId]);

        $handover = app(ProductionHandoverService::class)->push($po->fresh(), $merchandiser?->id ?? 1);

        $line = \ME\MerchandisingTrace\Models\Bridge\TrcPlanLine::with('sizes')->find($handover->plan_line_id);
        $qtyPerSize = ['S' => 1500, 'M' => 2000, 'L' => 1700, 'XL' => 800];
        foreach ($line->sizes as $lineSize) {
            $sizeName = Size::find($lineSize->size_id)?->name;
            $q = $qtyPerSize[$sizeName] ?? 0;
            $lineSize->update(['cut_qty' => $q, 'sewn_qty' => (int) ($q * 0.4), 'finished_qty' => (int) ($q * 0.1), 'passed_qty' => (int) ($q * 0.09), 'approved_qty' => (int) ($q * 0.09), 'packed_qty' => 0, 'shipped_qty' => 0, 'reject_qty' => (int) ($q * 0.01)]);
        }
        app(ProductionProgressSyncService::class)->syncFor($po->fresh());

        CommunicationLog::create(['style_id' => $style->id, 'sales_contract_po_id' => $po->id, 'log_date' => now()->subDays(21)->toDateString(), 'direction' => 'inbound', 'channel' => 'email', 'subject' => 'Fabric & trims being shipped directly to factory (CMT)', 'body' => 'This is a CMT order — we are shipping fabric and all trims directly to your factory. No need to raise a booking on your side.', 'created_by' => $merchandiser?->id]);

        $this->command?->info('Demo order (no purchase) seeded: ' . self::CONTRACT_NO . ' — PCD ' . strtoupper($tnaPlan->fresh()->pcd_result) . ', handed over to production.');
    }
}
