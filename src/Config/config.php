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
    ],
];
