<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use Illuminate\Database\Seeder;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\BomItem;
use ME\MerchandisingTrace\Models\Brand;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\Costing;
use ME\MerchandisingTrace\Models\Currency;
use ME\MerchandisingTrace\Models\Department;
use ME\MerchandisingTrace\Models\Document;
use ME\MerchandisingTrace\Models\Factory;
use ME\MerchandisingTrace\Models\Incoterm;
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\ItemCategory;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\PaymentTerm;
use ME\MerchandisingTrace\Models\Port;
use ME\MerchandisingTrace\Models\ProductType;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SampleType;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\Season;
use ME\MerchandisingTrace\Models\ShipMode;
use ME\MerchandisingTrace\Models\ShipmentPlan;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Models\TnaMilestone;
use ME\MerchandisingTrace\Models\Uom;
use ME\MerchandisingTrace\Models\WashType;

/**
 * Realistic demo data for the whole Merchandising module (masters, style
 * development, samples, BOM, orders). The `production-sfl` package's own
 * seeder consumes the Buyers/Styles/Colors/Sizes/Orders/BOMs this creates —
 * run this one first on a fresh database.
 */
class MerchandisingDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedNewMasters();

        $buyers = Buyer::factory()->count(5)->create();
        $brands = Brand::factory()->count(8)->create();
        $styles = Style::factory()->count(20)->create()->each(fn (Style $style) => $style->update([
            'buyer_id' => $buyers->random()->id,
            'brand_id' => $brands->random()->id,
        ]));
        $colors = Color::factory()->count(10)->create();
        $sizes = Size::factory()->count(6)->create();
        Uom::factory()->count(5)->create();
        Supplier::factory()->count(15)->create();
        Season::factory()->count(4)->create();
        Currency::factory()->count(4)->create();
        PaymentTerm::factory()->count(3)->create();
        Incoterm::factory()->count(5)->create();
        Port::factory()->count(6)->create();

        // Samples — created before order confirmation, so no order_id yet.
        foreach (range(1, 30) as $i) {
            Sample::factory()->create([
                'buyer_id' => $buyers->random()->id,
                'style_id' => $styles->random()->id,
                'size_id'  => fake()->boolean(60) ? $sizes->random()->id : null,
            ]);
        }

        // Orders + color/size breakdown
        $orders = collect(range(1, 50))->map(function () use ($buyers, $styles, $colors, $sizes) {
            $order = Order::factory()->create([
                'buyer_id' => $buyers->random()->id,
                'style_id' => $styles->random()->id,
            ]);

            foreach ($colors->random(min(2, $colors->count())) as $color) {
                foreach ($sizes->random(min(3, $sizes->count())) as $size) {
                    $order->items()->create([
                        'color_id' => $color->id,
                        'size_id'  => $size->id,
                        'qty'      => fake()->numberBetween(50, 500),
                    ]);
                }
            }
            $order->refreshOrderQty();

            return $order;
        });

        // Once a handful of orders exist, retroactively link a few approved
        // samples back to them (the normal flow once bulk order confirms).
        Sample::query()->status('approved')->inRandomOrder()->limit(10)->get()
            ->each(fn ($sample) => $sample->update(['order_id' => $orders->random()->id]));

        // Sales contracts for a subset of confirmed orders
        foreach ($orders->random(15) as $order) {
            SalesContract::factory()->create([
                'order_id' => $order->id,
                'buyer_id' => $order->buyer_id,
            ]);
        }

        // BOM for 15 of the 20 styles
        $stylesWithBom = $styles->random(15);
        foreach ($stylesWithBom as $style) {
            $bom = Bom::factory()->create(['style_id' => $style->id]);
            foreach (BomItem::ITEM_TYPES as $type) {
                if (fake()->boolean(70)) {
                    $bom->items()->create([
                        'item_type'     => $type,
                        'material_name' => ucfirst($type) . ' ' . fake()->word(),
                        'unit_id'       => Uom::query()->inRandomOrder()->value('id'),
                        'consumption'   => fake()->randomFloat(4, 0.01, 3),
                        'waste_percent' => fake()->randomFloat(2, 0, 8),
                    ]);
                }
            }
        }

        // Costing for 20 orders
        foreach ($orders->random(20) as $order) {
            Costing::factory()->create([
                'order_id' => $order->id,
                'status'   => fake()->randomElement(['draft', 'approved']),
            ]);
        }

        // TNA milestones — a handful per order, for a subset of orders
        $suppliers = Supplier::all();
        foreach ($orders->random(25) as $order) {
            foreach (fake()->randomElements(TnaMilestone::MILESTONES, fake()->numberBetween(2, 4)) as $milestone) {
                $plannedDate = fake()->dateTimeBetween('-15 days', '+15 days');
                TnaMilestone::factory()->create([
                    'order_id'       => $order->id,
                    'milestone_name' => $milestone,
                    'planned_date'   => $plannedDate->format('Y-m-d'),
                    'status'         => fake()->boolean(60) ? 'completed' : 'pending',
                ]);
            }
        }

        // Material bookings for 20 orders
        foreach ($orders->random(20) as $order) {
            MaterialBooking::factory()->create([
                'order_id'    => $order->id,
                'supplier_id' => $suppliers->isNotEmpty() ? $suppliers->random()->id : null,
                'unit_id'     => Uom::query()->inRandomOrder()->value('id'),
                'status'      => fake()->randomElement(['booked', 'received', 'cancelled']),
            ]);
        }

        // Shipment plans for 15 orders
        foreach ($orders->random(15) as $order) {
            ShipmentPlan::factory()->create([
                'order_id' => $order->id,
                'status'   => fake()->randomElement(['planned', 'shipped', 'delivered', 'delayed']),
            ]);
        }

        // Documents — a mix of buyer-level and order-level attachments
        foreach ($buyers as $buyer) {
            Document::create([
                'buyer_id'      => $buyer->id,
                'document_type' => 'buyer_document',
                'title'         => $buyer->name . ' — Vendor Agreement',
                'file_path'     => 'https://example.com/documents/' . strtolower($buyer->code) . '-agreement.pdf',
            ]);
        }
        foreach ($orders->random(15) as $order) {
            $type = fake()->randomElement(array_keys(Document::DOCUMENT_TYPES));
            Document::create([
                'order_id'      => $order->id,
                'document_type' => $type,
                'title'         => $order->po_number . ' — ' . Document::DOCUMENT_TYPES[$type],
                'file_path'     => 'https://example.com/documents/' . strtolower($order->po_number) . '-' . $type . '.pdf',
            ]);
        }
    }

    /**
     * §13 seed list: product types, wash types, ship modes, factories,
     * departments and the trims item catalog referenced by the T&A
     * "Sewing trims"/"Finishing Trims" groups.
     */
    private function seedNewMasters(): void
    {
        foreach ([
            ['code' => 'UJKT', 'name' => 'Unisex Jacket', 'category' => 'Woven'],
            ['code' => 'MPNT', 'name' => 'Mens Pant', 'category' => 'Woven'],
            ['code' => 'KPNT', 'name' => 'Kids Pant', 'category' => 'Woven'],
            ['code' => 'BAGY', 'name' => 'Baggy', 'category' => 'Woven'],
            ['code' => 'SKRT', 'name' => 'Skirtall', 'category' => 'Woven'],
            ['code' => 'SCHN', 'name' => 'Stretch Chino', 'category' => 'Woven'],
            ['code' => 'DJKT', 'name' => 'Denim Jacket', 'category' => 'Denim'],
            ['code' => 'JJEN', 'name' => 'Jogger Jean', 'category' => 'Denim'],
            ['code' => 'FLCE', 'name' => 'Fleece', 'category' => 'Knit'],
        ] as $row) {
            ProductType::firstOrCreate(['code' => $row['code']], $row);
        }

        foreach ([
            ['code' => 'ENZ', 'name' => 'Enzyme Wash'],
            ['code' => 'STN', 'name' => 'Stone Wash'],
            ['code' => 'RNS', 'name' => 'Rinse'],
            ['code' => 'GDY', 'name' => 'Garment Dye'],
            ['code' => 'NOW', 'name' => 'No Wash'],
        ] as $row) {
            WashType::firstOrCreate(['code' => $row['code']], $row);
        }

        foreach ([
            ['code' => 'SEA', 'name' => 'SEA'],
            ['code' => 'AIR', 'name' => 'AIR'],
            ['code' => 'SEA-AIR', 'name' => 'SEA-AIR'],
            ['code' => 'TRUCK', 'name' => 'TRUCK'],
        ] as $row) {
            ShipMode::firstOrCreate(['code' => $row['code']], $row);
        }

        foreach ([
            ['code' => 'DGL', 'name' => 'DGL', 'is_own' => true],
            ['code' => 'DAL-DGL', 'name' => 'DAL/DGL', 'is_own' => true],
        ] as $row) {
            Factory::firstOrCreate(['code' => $row['code']], $row);
        }

        foreach (['Merchandiser', 'Fabric', 'Trims', 'Sample', 'Wash', 'Production', 'Commercial', 'Buyer'] as $name) {
            Department::firstOrCreate(['code' => strtoupper(substr($name, 0, 4))], ['name' => $name]);
        }

        foreach ([
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
        ] as $row) {
            SampleType::firstOrCreate(['code' => $row['code']], $row);
        }

        $trimsCategory = ItemCategory::firstOrCreate(['code' => 'TRIMS'], ['name' => 'Trims']);
        foreach ([
            'Thread', 'Zipper', 'Main Label', 'Size Label', 'Care Label', 'Elastics', 'Buttons', 'Velcro',
            'Price Tag', 'Price Sticker', 'Cords', 'Poly Bag', 'Carton', 'Others',
        ] as $name) {
            Item::firstOrCreate(
                ['code' => strtoupper(str_replace(' ', '_', $name))],
                ['name' => $name, 'category_id' => $trimsCategory->id, 'type' => 'trim']
            );
        }
    }
}
