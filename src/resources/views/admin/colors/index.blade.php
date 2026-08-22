@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Colors',
    'routeBase' => 'merchandising-trace.colors',
    'permPrefix' => 'merch_color',
    'items' => $colors,
    'itemVar' => 'color',
    'columns' => ['name' => 'Name', 'code' => 'Code'],
    'fieldsView' => 'merchandising-trace::admin.colors.partials.fields',
    'modalLabel' => 'Color',
])
