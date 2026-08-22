@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Currencies',
    'routeBase' => 'merchandising-trace.currencies',
    'permPrefix' => 'merch_currency',
    'items' => $currencies,
    'itemVar' => 'currency',
    'columns' => ['code' => 'Code', 'name' => 'Name', 'symbol' => 'Symbol'],
    'fieldsView' => 'merchandising-trace::admin.currencies.partials.fields',
    'modalLabel' => 'Currency',
])
