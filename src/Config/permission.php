<?php

// Merged into config('permission')['modules']['MERCHANDISING'] at runtime by
// MerchandisingServiceProvider::mergePermissions(). Feeds the host's existing
// Roles Setup checkbox UI — permission strings used elsewhere are always
// "<module_key>.<action_key>", e.g. 'merch_style.list', 'merch_order.add'.

$crud = ['list' => 'List', 'add' => 'Create', 'edit' => 'Edit', 'view' => 'View', 'delete' => 'Delete', 'all' => 'All'];

return [
    'MERCHANDISING' => [
        'merch_dashboard' => [
            'label'       => 'Merchandising Dashboard',
            'permissions' => ['view' => 'View', 'all' => 'All'],
        ],
        'merch_report' => [
            'label'       => 'Merchandising Reports',
            'permissions' => ['view' => 'View', 'all' => 'All'],
        ],
        'merch_scope' => [
            'label'       => 'Merchandiser Row Scope',
            'permissions' => ['view_all' => 'View All Merchandisers\' Records (bypass own-orders scoping)'],
        ],

        // Master Setup
        'merch_buyer' => [
            'label'       => 'Buyers',
            'permissions' => $crud,
        ],
        'merch_brand' => [
            'label'       => 'Brands',
            'permissions' => $crud,
        ],
        'merch_supplier' => [
            'label'       => 'Suppliers',
            'permissions' => $crud,
        ],
        'merch_season' => [
            'label'       => 'Seasons',
            'permissions' => $crud,
        ],
        'merch_currency' => [
            'label'       => 'Currencies',
            'permissions' => $crud,
        ],
        'merch_payment_term' => [
            'label'       => 'Payment Terms',
            'permissions' => $crud,
        ],
        'merch_incoterm' => [
            'label'       => 'Incoterms',
            'permissions' => $crud,
        ],
        'merch_port' => [
            'label'       => 'Ports',
            'permissions' => $crud,
        ],
        'merch_uom' => [
            'label'       => 'Unit of Measure',
            'permissions' => $crud,
        ],
        'merch_product_type' => [
            'label'       => 'Product Types',
            'permissions' => $crud,
        ],
        'merch_wash_type' => [
            'label'       => 'Wash Types',
            'permissions' => $crud,
        ],
        'merch_ship_mode' => [
            'label'       => 'Ship Modes',
            'permissions' => $crud,
        ],
        'merch_factory' => [
            'label'       => 'Factories',
            'permissions' => $crud,
        ],
        'merch_department' => [
            'label'       => 'Departments',
            'permissions' => $crud,
        ],
        'merch_item_category' => [
            'label'       => 'Item Categories',
            'permissions' => $crud,
        ],
        'merch_item' => [
            'label'       => 'Items (BOM Library)',
            'permissions' => $crud,
        ],

        // Inquiry Management
        'merch_inquiry' => [
            'label'       => 'Inquiries',
            'permissions' => $crud,
        ],

        // Style Development
        'merch_style' => [
            'label'       => 'Styles',
            'permissions' => $crud,
        ],
        'merch_color' => [
            'label'       => 'Colors',
            'permissions' => $crud,
        ],
        'merch_size' => [
            'label'       => 'Sizes',
            'permissions' => $crud,
        ],

        // Sample Management
        'merch_sample' => [
            'label'       => 'Samples',
            'permissions' => $crud,
        ],
        'merch_sample_type' => [
            'label'       => 'Sample Types',
            'permissions' => $crud,
        ],

        // BOM
        'merch_bom' => [
            'label'       => 'Bill of Materials (BOM)',
            'permissions' => $crud,
        ],

        // Costing
        'merch_costing' => [
            'label'       => 'Costing',
            'permissions' => $crud,
        ],

        // Order Management
        'merch_order' => [
            'label'       => 'Buyer Orders',
            'permissions' => $crud,
        ],
        'merch_sales_contract' => [
            'label'       => 'Sales Contracts',
            'permissions' => $crud,
        ],

        // TNA
        'merch_tna' => [
            'label'       => 'TNA (Time & Action)',
            'permissions' => $crud,
        ],

        // Material Booking
        'merch_material_booking' => [
            'label'       => 'Material Booking',
            'permissions' => $crud,
        ],

        // Production Coordination (read-only)
        'merch_production_coordination' => [
            'label'       => 'Production Coordination',
            'permissions' => ['view' => 'View', 'all' => 'All'],
        ],

        // Shipment Management
        'merch_shipment_plan' => [
            'label'       => 'Shipment Management',
            'permissions' => $crud,
        ],

        // Document Management
        'merch_document' => [
            'label'       => 'Document Management',
            'permissions' => $crud,
        ],
    ],
];
