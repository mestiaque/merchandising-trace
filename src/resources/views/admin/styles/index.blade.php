@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Tech Packs / Styles',
    'routeBase' => 'merchandising-trace.styles',
    'permPrefix' => 'merch_style',
    'items' => $styles,
    'itemVar' => 'style',
    'columns' => ['style_no' => 'Style No', 'po_no' => 'PO No', 'name' => 'Name', 'buyer.name' => 'Buyer', 'development_status' => 'Dev Status'],
    'fieldsView' => 'merchandising-trace::admin.styles.partials.fields',
    'fieldsExtra' => [
        'buyersOptions' => $buyersOptions, 'seasonsOptions' => $seasonsOptions,
        'merchandisersOptions' => $merchandisersOptions, 'washTypesOptions' => $washTypesOptions,
        'productTypesOptions' => $productTypesOptions, 'inquiriesOptions' => $inquiriesOptions,
    ],
    'modalLabel' => 'Style',
    'viewRouteName' => 'merchandising-trace.styles.show',
])
