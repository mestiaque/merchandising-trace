<?php

return [
    'name' => 'MerchandisingTrace',

    'route' => [
        'prefix'     => 'admin/merchandising-trace',
        'as'         => 'merchandising-trace.',
        'middleware' => ['web', 'auth'],
    ],

    'company' => [
        'name' => env('COMPANY_NAME', 'Suhana Fashions Limited'),
    ],

    // Prefixes used by ME\MerchandisingTrace\Services\DocumentNumberService to
    // auto-generate unique document numbers (e.g. PO-000001, BOM-000001).
    'document_prefixes' => [
        'inquiry'      => 'INQ',
        'sample'       => 'SMP',
        'order'        => 'PO',
        'sales_contract' => 'SC',
        'bom'          => 'BOM',
        'costing'      => 'CST',
        'material_booking' => 'MB',
        'shipment_plan' => 'SP',
    ],
];
