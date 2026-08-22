@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Items (BOM Library)',
    'routeBase' => 'merchandising-trace.items',
    'permPrefix' => 'merch_item',
    'items' => $items,
    'itemVar' => 'item',
    'columns' => ['code' => 'Code', 'name' => 'Name', 'type' => 'Type', 'category.name' => 'Category'],
    'fieldsView' => 'merchandising-trace::admin.items.partials.fields',
    'fieldsExtra' => ['categoriesOptions' => $categoriesOptions, 'uomsOptions' => $uomsOptions, 'suppliersOptions' => $suppliersOptions],
    'modalLabel' => 'Item',
])
