@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Styles',
    'routeBase' => 'merchandising-trace.styles',
    'permPrefix' => 'merch_style',
    'items' => $styles,
    'itemVar' => 'style',
    'columns' => ['style_no' => 'Style No', 'name' => 'Name', 'buyer.name' => 'Buyer', 'development_status' => 'Dev Status'],
    'fieldsView' => 'merchandising-trace::admin.styles.partials.fields',
    'fieldsExtra' => [
        'buyersOptions' => $buyersOptions, 'seasonsOptions' => $seasonsOptions,
        'merchandisersOptions' => $merchandisersOptions, 'washTypesOptions' => $washTypesOptions,
        'productTypesOptions' => $productTypesOptions,
    ],
    'modalLabel' => 'Style',
    'viewRouteName' => 'merchandising-trace.styles.show',
])
