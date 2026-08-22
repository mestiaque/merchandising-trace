<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use Illuminate\Database\Seeder;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\Brand;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;

/**
 * Creates the Merchandising side (Buyer -> Style -> Sample -> Order -> BOM)
 * of the "one order, full lifecycle" demo — every master is firstOrCreate'd
 * under a "DEMO-" prefix so it's safe to re-run. Run this FIRST; then run
 * `ME\ProductionSfl\Database\Seeders\ProdCompleteOrderLifecycleSeeder`, which
 * looks up this seeder's demo Style/Order by their well-known codes and
 * drives Cutting -> ... -> Shipment (+ Sub Contract) from there.
 */
class MerchandisingLifecycleSeeder extends Seeder
{
    public function run(): void
    {
        $buyer = Buyer::firstOrCreate(
            ['code' => 'DEMO-BUY'],
            ['name' => 'Demo Buyer Ltd', 'contact_person' => 'John Buyer', 'phone' => '0123456789', 'is_active' => true]
        );

        $brand = Brand::firstOrCreate(['code' => 'DEMO-BRD'], ['name' => 'Demo Brand', 'is_active' => true]);

        $style = Style::firstOrCreate(
            ['style_no' => 'DEMO-STY-01'],
            ['name' => 'Demo Polo Shirt', 'buyer_id' => $buyer->id, 'brand_id' => $brand->id, 'description' => 'Full lifecycle demo style', 'is_active' => true]
        );

        $color = Color::firstOrCreate(['name' => 'Navy Blue'], ['is_active' => true]);
        $sizes = collect(['S', 'M', 'L', 'XL'])->map(fn ($name, $i) => Size::firstOrCreate(['name' => $name], ['sort_order' => $i + 1, 'is_active' => true]));

        // ---------- 1. Sample ----------
        $ppType = \ME\MerchandisingTrace\Models\SampleType::firstOrCreate(['code' => 'PP'], ['name' => 'PP (Pre-Production)', 'sequence' => 7, 'is_active' => true]);

        $sample = Sample::create([
            'buyer_id'        => $buyer->id,
            'style_id'        => $style->id,
            'sample_type_id'  => $ppType->id,
            'qty'             => 3,
            'size_id'         => $sizes[1]->id,
            'request_date'    => now()->subDays(35),
            'submission_date' => now()->subDays(30),
            'approval_date'   => now()->subDays(25),
            'status'          => 'approved',
            'remarks'         => 'PP sample approved for bulk production.',
        ]);

        // ---------- 2. Order (with color/size breakdown) ----------
        $order = Order::create([
            'buyer_id'      => $buyer->id,
            'style_id'      => $style->id,
            'description'   => 'Full lifecycle demo order',
            'delivery_date' => now()->addDays(20),
            'price'         => 4.50,
            'currency'      => 'USD',
            'status'        => 'confirmed',
            'remarks'       => 'End-to-end demo: Sample -> Order -> ... -> Shipment, plus Sub Contract.',
        ]);

        foreach ($sizes as $size) {
            $order->items()->create(['color_id' => $color->id, 'size_id' => $size->id, 'qty' => 2500]);
        }
        $order->refreshOrderQty(); // 4 sizes x 2500 = 10,000 pcs

        $sample->update(['order_id' => $order->id]);

        // ---------- 3. BOM & Consumption ----------
        $bom = Bom::create(['style_id' => $style->id, 'version' => 1, 'status' => 'active', 'remarks' => 'Demo BOM for ' . $style->name]);
        $bom->items()->createMany([
            ['item_type' => 'fabric', 'material_name' => 'Demo Cotton Fabric', 'consumption' => 1.2000, 'waste_percent' => 5],
            ['item_type' => 'thread', 'material_name' => 'Demo Sewing Thread', 'consumption' => 0.0500, 'waste_percent' => 2],
            ['item_type' => 'button', 'material_name' => 'Demo Button', 'consumption' => 5.0000, 'waste_percent' => 1],
            ['item_type' => 'label', 'material_name' => 'Demo Label', 'consumption' => 2.0000, 'waste_percent' => 0],
            ['item_type' => 'poly', 'material_name' => 'Demo Poly Bag', 'consumption' => 1.0000, 'waste_percent' => 0],
            ['item_type' => 'carton', 'material_name' => 'Demo Carton', 'consumption' => 0.0200, 'waste_percent' => 0],
        ]);
    }
}
