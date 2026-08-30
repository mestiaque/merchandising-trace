@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Style Image Types',
    'routeBase' => 'merchandising-trace.style-image-types',
    'permPrefix' => 'merch_style_image_type',
    'items' => $styleImageTypes,
    'itemVar' => 'record',
    'columns' => ['code' => 'Code', 'name' => 'Name'],
    'fieldsView' => 'merchandising-trace::admin.partials.code-name-fields',
    'fieldsExtra' => ['idPrefix' => 'styleImageType'],
    'modalLabel' => 'Image Type',
])
