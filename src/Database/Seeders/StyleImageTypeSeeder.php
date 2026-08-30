<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use Illuminate\Database\Seeder;
use ME\MerchandisingTrace\Models\StyleImageType;

/**
 * Seeds the same 5 values that used to be hardcoded on
 * StyleImageController::TYPES, now editable from Master Data > Style
 * Image Types instead of requiring a code change.
 */
class StyleImageTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'front' => 'Front',
            'back' => 'Back',
            'detail' => 'Detail',
            'embellishment' => 'Embellishment',
            'artwork' => 'Artwork',
        ] as $code => $name) {
            StyleImageType::firstOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }
    }
}
