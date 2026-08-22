@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Item Categories',
    'routeBase' => 'merchandising-trace.item-categories',
    'permPrefix' => 'merch_item_category',
    'items' => $itemCategories,
    'itemVar' => 'record',
    'columns' => ['code' => 'Code', 'name' => 'Name'],
    'fieldsView' => 'merchandising-trace::admin.partials.code-name-fields',
    'fieldsExtra' => ['idPrefix' => 'itemCategory'],
    'modalLabel' => 'Item Category',
])
