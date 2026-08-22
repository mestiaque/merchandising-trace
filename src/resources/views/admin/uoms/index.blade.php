@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Unit of Measure',
    'routeBase' => 'merchandising-trace.uoms',
    'permPrefix' => 'merch_uom',
    'items' => $uoms,
    'itemVar' => 'uom',
    'columns' => ['code' => 'Code', 'name' => 'Name', 'decimal_places' => 'Decimal Places'],
    'fieldsView' => 'merchandising-trace::admin.uoms.partials.fields',
    'modalLabel' => 'UOM',
])
