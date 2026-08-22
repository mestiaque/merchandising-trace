@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Sample Types',
    'routeBase' => 'merchandising-trace.sample-types',
    'permPrefix' => 'merch_sample_type',
    'items' => $sampleTypes,
    'itemVar' => 'record',
    'columns' => ['code' => 'Code', 'name' => 'Name', 'sequence' => 'Sequence'],
    'fieldsView' => 'merchandising-trace::admin.sample-types.partials.fields',
    'modalLabel' => 'Sample Type',
])
