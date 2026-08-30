<?php

namespace ME\MerchandisingTrace\Database\Seeders\Concerns;

use App\Models\User;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\BuyerContact;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\Currency;
use ME\MerchandisingTrace\Models\ExchangeRate;
use ME\MerchandisingTrace\Models\Factory;
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\ItemCategory;
use ME\MerchandisingTrace\Models\ProductType;
use ME\MerchandisingTrace\Models\Season;
use ME\MerchandisingTrace\Models\ShipMode;
use ME\MerchandisingTrace\Models\Size;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Models\Uom;
use ME\MerchandisingTrace\Models\WashType;

/**
 * Shared buyer/season/master setup for the 3 demo-order seeders
 * (DemoOrderNoRnDSeeder / DemoOrderFullProcessSeeder /
 * DemoOrderNoPurchaseSeeder) — all 3 orders sit under the same buyer/
 * season/product-type masters, only the style + order flow differ.
 */
trait SeedsDemoMasters
{
    protected function demoMasters(?int $merchandiserId): array
    {
        $buyer = Buyer::firstOrCreate(['code' => 'CORVEX'], [
            'name' => 'CORVEX', 'merchandiser_id' => $merchandiserId, 'region' => 'North America',
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
        $fabricItem = Item::firstOrCreate(['code' => 'MAINFAB-RUE'], [
            'name' => 'RUE Main Fabric — Twill 60/40', 'category_id' => $fabricCategory->id, 'type' => 'fabric',
            'uom_id' => $uomYard->id, 'default_supplier_id' => $fabricSupplier->id, 'default_price' => 3.20,
            'consumption_uom' => 'yds', 'is_active' => true,
        ]);

        return compact(
            'buyer', 'season', 'productType', 'color', 'sizeS', 'sizeM', 'sizeL', 'sizeXl',
            'washType', 'shipMode', 'factory', 'fabricSupplier', 'trimsSupplier', 'currency',
            'uomYard', 'fabricItem'
        );
    }

    protected function trcIds(): array
    {
        $trcProductId = \Illuminate\Support\Facades\DB::table('trc_products')->where('code', 'UJKT')->value('id')
            ?? \Illuminate\Support\Facades\DB::table('trc_products')->insertGetId(['code' => 'UJKT', 'name' => 'Unisex Jacket', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $trcSizeGroupId = \Illuminate\Support\Facades\DB::table('trc_size_groups')->where('name', 'S-M-L-XL')->value('id')
            ?? \Illuminate\Support\Facades\DB::table('trc_size_groups')->insertGetId(['name' => 'S-M-L-XL', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $trcPartId = \Illuminate\Support\Facades\DB::table('trc_parts')->where('code', 'CHEST')->value('id')
            ?? \Illuminate\Support\Facades\DB::table('trc_parts')->insertGetId(['code' => 'CHEST', 'name' => 'Chest Panel', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);

        return compact('trcProductId', 'trcSizeGroupId', 'trcPartId');
    }
}
