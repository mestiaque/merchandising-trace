<?php

$base = '/admin/merchandising-trace';

return [
    [
        'group_title' => '',
        [
            'title'      => 'Merchandising',
            'icon'       => 'fa-solid fa-shirt',
            'icon_color' => 'text-primary',
            'permission' => '',
            'order'      => 10,
            'children'   => [
                [
                    'title'      => 'Masters',
                    'icon'       => 'fa-solid fa-gear',
                    'icon_color' => 'text-secondary',
                    'permission' => '',
                    'children'   => [
                        ['title' => 'Buyers', 'icon' => 'fa-solid fa-handshake', 'icon_color' => 'text-info', 'permission' => 'merch_buyer', 'route' => "$base/buyers"],
                        ['title' => 'Seasons', 'icon' => 'fa-solid fa-snowflake', 'icon_color' => 'text-info', 'permission' => 'merch_season', 'route' => "$base/seasons"],
                        ['title' => 'Product Types', 'icon' => 'fa-solid fa-shapes', 'icon_color' => 'text-info', 'permission' => 'merch_product_type', 'route' => "$base/product-types"],
                        ['title' => 'Colors', 'icon' => 'fa-solid fa-palette', 'icon_color' => 'text-info', 'permission' => 'merch_color', 'route' => "$base/colors"],
                        ['title' => 'Sizes', 'icon' => 'fa-solid fa-ruler-combined', 'icon_color' => 'text-info', 'permission' => 'merch_size', 'route' => "$base/sizes"],
                        ['title' => 'Wash Types', 'icon' => 'fa-solid fa-soap', 'icon_color' => 'text-info', 'permission' => 'merch_wash_type', 'route' => "$base/wash-types"],
                        ['title' => 'Ship Modes', 'icon' => 'fa-solid fa-plane-departure', 'icon_color' => 'text-info', 'permission' => 'merch_ship_mode', 'route' => "$base/ship-modes"],
                        ['title' => 'Factories', 'icon' => 'fa-solid fa-industry', 'icon_color' => 'text-info', 'permission' => 'merch_factory', 'route' => "$base/factories"],
                        ['title' => 'Suppliers', 'icon' => 'fa-solid fa-truck-field', 'icon_color' => 'text-info', 'permission' => 'merch_supplier', 'route' => "$base/suppliers"],
                        ['title' => 'Currencies', 'icon' => 'fa-solid fa-money-bill', 'icon_color' => 'text-info', 'permission' => 'merch_currency', 'route' => "$base/currencies"],
                        ['title' => 'Item Categories', 'icon' => 'fa-solid fa-layer-group', 'icon_color' => 'text-info', 'permission' => 'merch_item_category', 'route' => "$base/item-categories"],
                        ['title' => 'Items (BOM Library)', 'icon' => 'fa-solid fa-boxes-stacked', 'icon_color' => 'text-info', 'permission' => 'merch_item', 'route' => "$base/items"],
                        ['title' => 'Unit of Measure', 'icon' => 'fa-solid fa-ruler', 'icon_color' => 'text-info', 'permission' => 'merch_uom', 'route' => "$base/uoms"],
                        ['title' => 'Departments', 'icon' => 'fa-solid fa-sitemap', 'icon_color' => 'text-info', 'permission' => 'merch_department', 'route' => "$base/departments"],
                    ],
                ],
                [
                    'title'      => 'Inquiries',
                    'icon'       => 'fa-solid fa-magnifying-glass-dollar',
                    'icon_color' => 'text-danger',
                    'permission' => 'merch_inquiry',
                    'route'      => "$base/inquiries",
                ],
                [
                    'title'      => 'Styles',
                    'icon'       => 'fa-solid fa-vest-patches',
                    'icon_color' => 'text-warning',
                    'permission' => 'merch_style',
                    'route'      => "$base/styles",
                ],
                [
                    'title'      => 'Samples',
                    'icon'       => 'fa-solid fa-vial',
                    'icon_color' => 'text-danger',
                    'permission' => 'merch_sample',
                    'route'      => "$base/samples",
                ],
                [
                    'title'      => 'Sample Types',
                    'icon'       => 'fa-solid fa-list-ol',
                    'icon_color' => 'text-danger',
                    'permission' => 'merch_sample_type',
                    'route'      => "$base/sample-types",
                ],
                [
                    'title'      => 'BOM',
                    'icon'       => 'fa-solid fa-list-check',
                    'icon_color' => 'text-danger',
                    'permission' => 'merch_bom',
                    'route'      => "$base/boms",
                ],
                [
                    'title'      => 'Costing',
                    'icon'       => 'fa-solid fa-calculator',
                    'icon_color' => 'text-danger',
                    'permission' => 'merch_costing',
                    'route'      => "$base/cost-sheets",
                ],
                [
                    'title'      => 'Sales Contracts',
                    'icon'       => 'fa-solid fa-file-signature',
                    'icon_color' => 'text-danger',
                    'permission' => 'merch_sales_contract',
                    'route'      => "$base/sales-contracts",
                ],
            ],
        ],
    ],
];
