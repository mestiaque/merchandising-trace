<?php

use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\Currency;
use ME\MerchandisingTrace\Models\Department;
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
 * §M01 AC: "Excel import/export" for every master table. One generic
 * controller (MasterExcelController) drives all 14 from this config
 * rather than duplicating export/import logic per master. `unique` is
 * the column import upserts on; `columns` lists only the master's own
 * SCALAR fields (label => attribute) -- foreign-key relations (e.g.
 * Item.category_id, Buyer.merchandiser_id) are intentionally left out of
 * bulk import/export and stay a manual-form field, to avoid silently
 * mis-linking a row from an ambiguous imported code.
 */
return [
    'buyers' => [
        'model' => Buyer::class, 'permPrefix' => 'merch_buyer', 'unique' => 'code',
        'columns' => [
            'Code' => 'code', 'Name' => 'name', 'Region' => 'region', 'Agent Name' => 'agent_name',
            'Address' => 'address', 'Contact Person' => 'contact_person', 'Phone' => 'phone', 'Email' => 'email',
            'Payment Term' => 'payment_term', 'Delivery Term' => 'delivery_term', 'Default AQL' => 'default_aql',
        ],
    ],
    'seasons' => [
        'model' => Season::class, 'permPrefix' => 'merch_season', 'unique' => 'code',
        'columns' => ['Code' => 'code', 'Name' => 'name', 'Year' => 'year', 'Start Date' => 'start_date', 'End Date' => 'end_date'],
    ],
    'product-types' => [
        'model' => ProductType::class, 'permPrefix' => 'merch_product_type', 'unique' => 'code',
        'columns' => ['Code' => 'code', 'Name' => 'name', 'Category' => 'category', 'Default SMV' => 'default_smv'],
    ],
    'colors' => [
        'model' => Color::class, 'permPrefix' => 'merch_color', 'unique' => 'code',
        'columns' => ['Code' => 'code', 'Name' => 'name'],
    ],
    'sizes' => [
        'model' => Size::class, 'permPrefix' => 'merch_size', 'unique' => 'name',
        'columns' => ['Name' => 'name', 'Sort Order' => 'sort_order'],
    ],
    'wash-types' => [
        'model' => WashType::class, 'permPrefix' => 'merch_wash_type', 'unique' => 'code',
        'columns' => ['Code' => 'code', 'Name' => 'name'],
    ],
    'ship-modes' => [
        'model' => ShipMode::class, 'permPrefix' => 'merch_ship_mode', 'unique' => 'code',
        'columns' => ['Code' => 'code', 'Name' => 'name'],
    ],
    'factories' => [
        'model' => Factory::class, 'permPrefix' => 'merch_factory', 'unique' => 'code',
        'columns' => [
            'Code' => 'code', 'Name' => 'name', 'Address' => 'address', 'Unit Type' => 'unit_type',
            'Capacity Per Month' => 'capacity_per_month',
        ],
    ],
    'suppliers' => [
        'model' => Supplier::class, 'permPrefix' => 'merch_supplier', 'unique' => 'code',
        'columns' => [
            'Code' => 'code', 'Name' => 'name', 'Type' => 'type', 'Country' => 'country', 'Contact' => 'contact',
            'Lead Time Days' => 'lead_time_days', 'Payment Term' => 'payment_term', 'Rating' => 'rating',
        ],
    ],
    'currencies' => [
        'model' => Currency::class, 'permPrefix' => 'merch_currency', 'unique' => 'code',
        'columns' => ['Code' => 'code', 'Name' => 'name', 'Symbol' => 'symbol'],
    ],
    'item-categories' => [
        'model' => ItemCategory::class, 'permPrefix' => 'merch_item_category', 'unique' => 'code',
        'columns' => ['Code' => 'code', 'Name' => 'name'],
    ],
    'items' => [
        'model' => Item::class, 'permPrefix' => 'merch_item', 'unique' => 'code',
        'columns' => ['Code' => 'code', 'Name' => 'name', 'Type' => 'type', 'Default Price' => 'default_price', 'Consumption UOM' => 'consumption_uom'],
    ],
    'uoms' => [
        'model' => Uom::class, 'permPrefix' => 'merch_uom', 'unique' => 'code',
        'columns' => ['Code' => 'code', 'Name' => 'name', 'Decimal Places' => 'decimal_places'],
    ],
    'departments' => [
        'model' => Department::class, 'permPrefix' => 'merch_department', 'unique' => 'code',
        'columns' => ['Code' => 'code', 'Name' => 'name'],
    ],
];
