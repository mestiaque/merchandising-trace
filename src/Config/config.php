<?php

return [
    'name' => 'MerchandisingTrace',

    'route' => [
        'prefix'     => 'admin/merchandising-trace',
        'as'         => 'merchandising-trace.',
        'middleware' => ['web', 'auth'],
    ],

    // Prefixes used by ME\MerchandisingTrace\Services\DocumentNumberService
    // to auto-generate unique document numbers (e.g. INQ-000001, PO-000001).
    'document_prefixes' => [
        'inquiry' => 'INQ',
        'sample'  => 'SMP',
        'order'   => 'PO',
        'bom'     => 'BOM',
        'costing' => 'CST',
        'sales_contract' => 'SC',
        'tna' => 'TNA',
        'tna_sub_plan' => 'SUB',
    ],

    // Letterhead printed on the Open Cost Sheet (screen, print and PDF).
    // logo: a path on the "public" storage disk, or null to print the short name.
    'company' => [
        'name' => env('MERCH_COMPANY_NAME', 'SUHANA FASHIONS LTD'),
        'short' => env('MERCH_COMPANY_SHORT', 'SFL'),
        'logo' => env('MERCH_COMPANY_LOGO'),
    ],

    // Who is emailed when a new buyer / supplier / submitted sample needs approval. Leave a list
    // empty to notify every user holding that master's '.approve' permission
    // (merch_buyer.approve / merch_supplier.approve); fill it in to notify
    // exactly these addresses instead.
    'approval_recipients' => [
        'merchandising.buyer' => [],
        'merchandising.supplier' => [],
        // Samples: notified when a sample is submitted (default: holders of merch_sample.approve).
        'merchandising.sample' => [],
    ],
];
