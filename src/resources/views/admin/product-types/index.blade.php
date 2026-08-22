@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Product Types',
    'routeBase' => 'merchandising-trace.product-types',
    'permPrefix' => 'merch_product_type',
    'items' => $productTypes,
    'itemVar' => 'productType',
    'columns' => ['code' => 'Code', 'name' => 'Name', 'category' => 'Category', 'default_smv' => 'Default SMV'],
    'fieldsView' => 'merchandising-trace::admin.product-types.partials.fields',
    'modalLabel' => 'Product Type',
])
