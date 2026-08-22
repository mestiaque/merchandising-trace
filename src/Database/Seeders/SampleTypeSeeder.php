<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use Illuminate\Database\Seeder;
use ME\MerchandisingTrace\Models\SampleType;

/**
 * §13: Proto, Fit, 2nd Fit, Size Set, SMS, 1st PP, PP, TOP, Shipment, Wash
 * Standard, Shade Band (+ Salesman, Photo Shoot from §4.4's fuller list).
 * Codes here must match DefaultTnaTemplateSeeder's auto_source_ref values.
 */
class SampleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'PROTO', 'name' => 'Proto', 'sequence' => 1],
            ['code' => 'FIT', 'name' => 'Fit', 'sequence' => 2],
            ['code' => 'FIT2', 'name' => '2nd Fit', 'sequence' => 3],
            ['code' => 'SIZESET', 'name' => 'Size Set', 'sequence' => 4],
            ['code' => 'SMS', 'name' => 'SMS', 'sequence' => 5],
            ['code' => 'SALESMAN', 'name' => 'Salesman', 'sequence' => 6],
            ['code' => 'PP1', 'name' => '1st PP', 'sequence' => 7],
            ['code' => 'PP', 'name' => 'PP (Pre-Production)', 'sequence' => 8],
            ['code' => 'TOP', 'name' => 'TOP', 'sequence' => 9],
            ['code' => 'SHIPMENT', 'name' => 'Shipment', 'sequence' => 10],
            ['code' => 'PHOTOSHOOT', 'name' => 'Photo Shoot', 'sequence' => 11],
            ['code' => 'WASHSTD', 'name' => 'Wash Standard', 'sequence' => 12],
            ['code' => 'SHADEBAND', 'name' => 'Shade Band', 'sequence' => 13],
        ];

        foreach ($rows as $row) {
            SampleType::firstOrCreate(['code' => $row['code']], $row + ['is_active' => true]);
        }
    }
}
