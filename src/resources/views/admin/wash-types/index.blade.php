@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Wash Types',
    'routeBase' => 'merchandising-trace.wash-types',
    'permPrefix' => 'merch_wash_type',
    'items' => $washTypes,
    'itemVar' => 'record',
    'columns' => ['code' => 'Code', 'name' => 'Name'],
    'fieldsView' => 'merchandising-trace::admin.partials.code-name-fields',
    'fieldsExtra' => ['idPrefix' => 'washType'],
    'modalLabel' => 'Wash Type',
])
