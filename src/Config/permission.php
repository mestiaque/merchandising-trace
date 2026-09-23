<?php

// Merged into config('permission')['modules']['MERCHANDISING_TRACE'] at
// runtime by MerchandisingServiceProvider::mergePermissions(). Permission
// strings used elsewhere are always "<module_key>.<action_key>", e.g.
// 'merch_buyer.add'.

$crud = ['list' => 'List', 'add' => 'Create', 'edit' => 'Edit', 'view' => 'View', 'delete' => 'Delete', 'all' => 'All'];

return [
    'MERCHANDISING_TRACE' => [
        'merch_scope' => [
            'label'       => 'Merchandiser Row Scope',
            'permissions' => ['view_all' => "View All Merchandisers' Records (bypass own-orders scoping)"],
        ],

        // Masters
        'merch_buyer' => ['label' => 'Buyers', 'permissions' => $crud],
        'merch_season' => ['label' => 'Seasons', 'permissions' => $crud],
        'merch_product_type' => ['label' => 'Product Types', 'permissions' => $crud],
        'merch_color' => ['label' => 'Colors', 'permissions' => $crud],
        'merch_size' => ['label' => 'Sizes', 'permissions' => $crud],
        'merch_wash_type' => ['label' => 'Wash Types', 'permissions' => $crud],
        'merch_ship_mode' => ['label' => 'Ship Modes', 'permissions' => $crud],
        'merch_factory' => ['label' => 'Factories', 'permissions' => $crud],
        'merch_supplier' => ['label' => 'Suppliers', 'permissions' => $crud],
        'merch_currency' => ['label' => 'Currencies', 'permissions' => $crud],
        'merch_item_category' => ['label' => 'Item Categories', 'permissions' => $crud],
        'merch_item' => ['label' => 'Items (BOM Library)', 'permissions' => $crud],
        'merch_uom' => ['label' => 'Unit of Measure', 'permissions' => $crud],
        'merch_department' => ['label' => 'Departments', 'permissions' => $crud],
        'merch_style_image_type' => ['label' => 'Style Image Types', 'permissions' => $crud],

        // Inquiry & Style Development
        'merch_inquiry' => ['label' => 'Inquiries', 'permissions' => $crud],
        'merch_style' => ['label' => 'Styles', 'permissions' => $crud],
        'merch_risk_assessment' => ['label' => 'Risk Assessment', 'permissions' => $crud],

        // Sample Management
        'merch_sample' => ['label' => 'Samples', 'permissions' => $crud],
        'merch_sample_type' => ['label' => 'Sample Types', 'permissions' => $crud],

        // BOM & Consumption
        'merch_bom' => ['label' => 'Bill of Materials (BOM)', 'permissions' => $crud],

        // Costing
        'merch_costing' => ['label' => 'Costing', 'permissions' => $crud],

        // Sales Contract
        'merch_sales_contract' => ['label' => 'Sales Contracts', 'permissions' => $crud],

        // T&A
        'merch_tna' => [
            'label'       => 'T&A',
            'permissions' => $crud + ['override_pcd' => 'Override PCD Result'],
        ],

        // Material Booking
        'merch_material_booking' => ['label' => 'Material Booking', 'permissions' => $crud],

        // Production Handover Bridge
        'merch_production_handover' => ['label' => 'Production Handover', 'permissions' => $crud],

        // Shipment Plan
        'merch_shipment_plan' => ['label' => 'Shipment Plan', 'permissions' => $crud],

        // Documentation
        'merch_documentation' => ['label' => 'Documentation', 'permissions' => $crud],

        // Buyer Communication
        'merch_communication' => ['label' => 'Buyer Communication', 'permissions' => $crud],

        // Dashboards & Reports
        'merch_dashboard' => [
            'label' => 'Dashboards',
            'permissions' => ['view' => 'View own dashboard', 'view_all' => 'View management dashboard'],
        ],
        'merch_reports' => ['label' => 'Reports', 'permissions' => ['list' => 'List', 'view' => 'View/Export']],

        // Cross-module 360° History (Merchandising + Production + Inventory)
        'merch_history' => ['label' => '360° History', 'permissions' => ['list' => 'Search', 'view' => 'View Timeline']],
    ],
];
