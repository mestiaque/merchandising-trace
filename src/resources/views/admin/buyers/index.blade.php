@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Buyers',
    'routeBase' => 'merchandising-trace.buyers',
    'permPrefix' => 'merch_buyer',
    'items' => $buyers,
    'itemVar' => 'buyer',
    'columns' => ['code' => 'Code', 'name' => 'Name', 'merchandiser.name' => 'Merchandiser', 'phone' => 'Phone'],
    'fieldsView' => 'merchandising-trace::admin.buyers.partials.fields',
    'fieldsExtra' => ['merchandisersOptions' => $merchandisersOptions],
    'modalLabel' => 'Buyer',
])
