@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Factories',
    'routeBase' => 'merchandising-trace.factories',
    'permPrefix' => 'merch_factory',
    'items' => $factories,
    'itemVar' => 'factory',
    'columns' => ['code' => 'Code', 'name' => 'Name', 'unit_type' => 'Unit Type', 'capacity_per_month' => 'Capacity/Month'],
    'fieldsView' => 'merchandising-trace::admin.factories.partials.fields',
    'modalLabel' => 'Factory',
])
