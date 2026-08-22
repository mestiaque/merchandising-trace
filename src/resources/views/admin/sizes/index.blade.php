@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Sizes',
    'routeBase' => 'merchandising-trace.sizes',
    'permPrefix' => 'merch_size',
    'items' => $sizes,
    'itemVar' => 'size',
    'columns' => ['name' => 'Name', 'sort_order' => 'Sort Order'],
    'fieldsView' => 'merchandising-trace::admin.sizes.partials.fields',
    'modalLabel' => 'Size',
])
