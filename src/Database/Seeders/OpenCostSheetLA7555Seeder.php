<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\Currency;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Models\Uom;

/**
 * The factory's reference "Open Cost Sheet" (KPHONGRI (ADA) / LA7555 denim
 * jacket, 6000 pcs, 25-Jul-26) entered line by line, so the app's show /
 * print / PDF can be compared against the original. Costed without a tech
 * pack (style_ref only), as on the paper sheet. Re-running replaces the
 * sheet's lines instead of duplicating it.
 *
 *   php artisan db:seed --class="ME\MerchandisingTrace\Database\Seeders\OpenCostSheetLA7555Seeder"
 */
class OpenCostSheetLA7555Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $buyer = Buyer::where('name', 'KPHONGRI (ADA)')->first()
                ?? Buyer::firstOrCreate(['code' => 'KPHONGRI'], ['name' => 'KPHONGRI (ADA)', 'is_active' => true]);

            $usd = Currency::firstOrCreate(['code' => 'USD'], ['name' => 'US Dollar', 'symbol' => '$', 'is_active' => true]);
            $yds = Uom::firstOrCreate(['code' => 'YDS'], ['name' => 'Yards', 'decimal_places' => 2, 'is_active' => true]);
            $dzn = Uom::firstOrCreate(['code' => 'DZN'], ['name' => 'Dozen', 'decimal_places' => 2, 'is_active' => true]);

            foreach ([
                ['FAIZA-BZ', 'Faiza Button and Zipper Ltd', 'trims'],
                ['ETASIA-IL', 'ETASIA INTERLINING', 'trims'],
                ['DENIMART-WP', 'Denim Art washing Plant', 'wash'],
            ] as [$code, $name, $type]) {
                Supplier::where('name', $name)->exists()
                    || Supplier::create(['code' => $code, 'name' => $name, 'type' => $type, 'country' => 'Bangladesh', 'is_active' => true]);
            }

            $sheet = CostSheet::updateOrCreate(['cost_sheet_no' => 'CST-LA7555'], [
                'style_id' => null,
                'inquiry_id' => null,
                'style_ref' => 'LA7555',
                'garment_description' => 'DENIM JACKET',
                'size_range' => null,
                'costing_date' => '2026-07-25',
                'buyer_id' => $buyer->id,
                'version' => 1,
                'currency_id' => $usd->id,
                'exchange_rate' => 1,
                'order_qty' => 6000,
                // CM 37.97 / dz = SMV 45.2 × CPM 0.07 × 12 (100% efficiency).
                'smv' => 45.2,
                'cm_minute_rate' => 0.07,
                'efficiency_percent' => 100,
                'cm_cost' => 0,
                'commercial_percent' => 5,
                'price_type' => 'FOB',
                'status' => 'draft',
                'prepared_by' => User::query()->value('id'),
            ]);

            // [group, description, supplier, consumption / dz, unit, unit price]
            $lines = [
                ['fabric', 'SHELL FABRIC : A1018-5', 'Buyer nominated', 15.60, $yds, 1.86],
                ['fabric', 'FUSING', 'ETASIA INTERLINING', 5.50, $yds, 0.25],

                ['trims', 'SEWING THREAD', null, 1.60, $dzn, 0.0625],
                ['trims', 'BUTTON-1, 20 mm', 'Faiza Button and Zipper Ltd', 14.05, $dzn, 0.0764],
                ['trims', 'ZIPPER #5', 'Faiza Button and Zipper Ltd', 1.05, $dzn, 0.6667],
                ['trims', 'MAIN LABEL-NNLML-07A', null, 1.05, $dzn, 0.0208],
                ['trims', 'SIZE LABEL- NNL5Z-06BLK', null, 1.05, $dzn, 0.0100],
                ['trims', 'HANG TAG- NNLHT-05PNK', null, 1.05, $dzn, 0.0333],
                ['trims', 'CARE LABEL- NNLCL-05', null, 1.05, $dzn, 0.0167],
                ['trims', 'PRICE STICKER', null, 1.05, $dzn, 0.0083],
                ['trims', 'LICENSEE LABEL', null, 1.05, $dzn, 0.0167],
                ['trims', 'DISCLAIMER TAG', null, 1.05, $dzn, 0.0292],
                ['trims', 'POLY', null, 1.05, $dzn, 0.0667],
                ['trims', 'CARTON', null, 1.05, $dzn, 0.1500],
                ['trims', 'RISK FUND', null, 1.05, $dzn, 0.0417],

                ['wash', 'WASH-FABIENNE', 'Denim Art washing Plant', 1.05, null, 1.00],
                // D stone / E print / F heat seal are blank on the sheet.
            ];

            $sheet->items()->delete();
            foreach ($lines as [$group, $description, $supplier, $consumption, $uom, $rate]) {
                $sheet->items()->create([
                    'group' => $group,
                    'description' => $description,
                    'supplier_name' => $supplier,
                    'consumption' => $consumption,
                    'uom_id' => $uom?->id,
                    'rate' => $rate,
                ]);
            }

            $sheet->recompute();
        });
    }
}
