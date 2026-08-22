<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use Illuminate\Database\Seeder;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\SalesContractPoSize;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Services\DocumentChecklistService;
use ME\MerchandisingTrace\Services\MaterialBookingTnaSyncService;
use ME\MerchandisingTrace\Services\PcdGateService;
use ME\MerchandisingTrace\Services\SampleTnaSyncService;
use ME\MerchandisingTrace\Services\TnaPlanGenerationService;

/**
 * A SECOND demo order on the same RUE1 style DemoDataSeeder already set up
 * (approved BOM/cost sheet/samples, fabric+trims fully booked, style
 * already mapped to its production Product/Size Group) -- everything a PO
 * needs to pass the pre-flight checklist -- but this one is deliberately
 * left un-handed-over, so it shows up on the "Handover to Production"
 * screen for someone to walk through the button themselves.
 */
class DemoHandoverReadyOrderSeeder extends Seeder
{
    private const CONTRACT_NO = 'DEMO-CORVEX-4500396949';

    public function run(): void
    {
        if (SalesContract::where('contract_no', self::CONTRACT_NO)->exists()) {
            $this->command?->info('Second demo order already exists — skipping.');

            return;
        }

        $style = Style::where('style_no', 'RUE1')->first();
        if (! $style) {
            $this->command?->warn('Style RUE1 not found — run DemoDataSeeder first.');

            return;
        }

        $merchandiser = $style->merchandiser_id;
        $color = Color::where('code', 'BLK')->first() ?? Color::first();
        $sizeS = Size::where('name', 'S')->first();
        $sizeM = Size::where('name', 'M')->first();
        $sizeL = Size::where('name', 'L')->first();
        $sizeXl = Size::where('name', 'XL')->first();

        $contract = SalesContract::create([
            'contract_no' => self::CONTRACT_NO, 'buyer_id' => $style->buyer_id, 'season_id' => $style->season_id,
            'merchandiser_id' => $merchandiser, 'factory_id' => null,
            'buyer_order_ref' => 'CORVEX-PO-4500396949', 'contract_date' => now()->subDays(10)->toDateString(),
            'delivery_term' => 'FOB', 'payment_term' => 'LC at sight', 'status' => 'draft',
        ]);

        $po = SalesContractPo::create([
            'sales_contract_id' => $contract->id, 'style_id' => $style->id, 'color_id' => $color->id,
            'wash_type_id' => $style->wash_type_id, 'po_no' => '4500396949',
            'po_due_date' => now()->subDays(5)->toDateString(), 'po_qty' => 5000, 'unit_price' => 4.82,
            'total_value' => 5000 * 4.82, 'price_type' => 'FOB', 'cost_smv' => 58.84, 'cm' => 3.00, 'fob_foc' => 4.82,
            'pcd_date' => now()->addDays(3)->toDateString(), 'shipment_date' => now()->addDays(33)->toDateString(),
            'print_emb' => 'no', 'emb_applique_ih' => 'no', 'studs_stones_ih' => 'no', 'heat_seal_ih' => 'yes',
            'status' => 'pending',
        ]);
        foreach ([$sizeS->id => 1000, $sizeM->id => 1500, $sizeL->id => 1500, $sizeXl->id => 1000] as $sizeId => $qty) {
            SalesContractPoSize::create(['sales_contract_po_id' => $po->id, 'size_id' => $sizeId, 'qty' => $qty]);
        }

        $contract->update(['status' => 'confirmed']);
        $contract->refreshTotals();

        $tnaPlan = app(TnaPlanGenerationService::class)->generateFor($po);
        $po->update(['status' => 'tna_created']);
        app(DocumentChecklistService::class)->generateFor($contract);

        // Pull in everything already true for this style/samples/bookings —
        // clears every blocking task without re-doing the underlying work.
        $syncTna = app(SampleTnaSyncService::class);
        Sample::where('style_id', $style->id)->where('status', 'approved')->get()
            ->each(fn ($s) => $syncTna->syncFromSample($s));

        $syncMaterial = app(MaterialBookingTnaSyncService::class);
        MaterialBooking::where('style_id', $style->id)->get()->each(function (MaterialBooking $booking) use ($syncMaterial) {
            $syncMaterial->syncFromBooking($booking);
            $booking->receipts()->get()->each(fn ($r) => $syncMaterial->syncFromReceipt($r));
            $booking->consignments()->get()->each(fn ($c) => $syncMaterial->syncFromConsignment($c));
        });

        // The remaining, purely-manual Pilot Status tasks.
        $tnaPlan->tasks()->whereIn('task_code', ['file_handover', 'pullout', 'pp_meeting'])
            ->update(['actual_date' => now()->subDay(), 'status' => 'done']);

        app(PcdGateService::class)->evaluate($tnaPlan->fresh());

        $this->command?->info(
            'Second demo order ready for MANUAL handover: ' . self::CONTRACT_NO . ' / PO ' . $po->po_no
            . ' — PCD ' . strtoupper($tnaPlan->fresh()->pcd_result) . ', NOT yet pushed to production.'
        );
    }
}
