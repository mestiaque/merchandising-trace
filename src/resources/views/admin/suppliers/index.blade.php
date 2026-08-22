@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Suppliers',
    'routeBase' => 'merchandising-trace.suppliers',
    'permPrefix' => 'merch_supplier',
    'items' => $suppliers,
    'itemVar' => 'supplier',
    'columns' => ['code' => 'Code', 'name' => 'Name', 'type' => 'Type', 'country' => 'Country', 'lead_time_days' => 'Lead Time (days)'],
    'fieldsView' => 'merchandising-trace::admin.suppliers.partials.fields',
    'modalLabel' => 'Supplier',
])
