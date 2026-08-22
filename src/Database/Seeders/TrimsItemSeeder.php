<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use Illuminate\Database\Seeder;
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\ItemCategory;

/**
 * §4.1: "Seed trims from the T&A sheet". Codes must match
 * DefaultTnaTemplateSeeder's auto_source_ref values for Sewing/Finishing
 * Trims tasks so MaterialBookingTnaSyncService can match receipts to tasks.
 */
class TrimsItemSeeder extends Seeder
{
    public function run(): void
    {
        $category = ItemCategory::firstOrCreate(['code' => 'TRIMS'], ['name' => 'Trims', 'is_active' => true]);

        $rows = [
            'THREAD' => 'Thread', 'ZIPPER' => 'Zipper', 'MAIN_LABEL' => 'Main Label',
            'SIZE_LABEL' => 'Size Label', 'CARE_LABEL' => 'Care Label', 'ELASTICS' => 'Elastics',
            'BUTTONS' => 'Buttons', 'VELCRO' => 'Velcro', 'PRICE_TAG' => 'Price Tag',
            'PRICE_STICKER' => 'Price Sticker', 'CORDS' => 'Cords', 'POLY_BAG' => 'Poly Bag',
            'CARTON' => 'Carton', 'OTHERS' => 'Others',
        ];

        foreach ($rows as $code => $name) {
            Item::firstOrCreate(['code' => $code], [
                'name' => $name,
                'category_id' => $category->id,
                'type' => 'trim',
                'is_active' => true,
            ]);
        }
    }
}
