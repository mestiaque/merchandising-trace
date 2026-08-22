@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Departments',
    'routeBase' => 'merchandising-trace.departments',
    'permPrefix' => 'merch_department',
    'items' => $departments,
    'itemVar' => 'record',
    'columns' => ['code' => 'Code', 'name' => 'Name'],
    'fieldsView' => 'merchandising-trace::admin.partials.code-name-fields',
    'fieldsExtra' => ['idPrefix' => 'department'],
    'modalLabel' => 'Department',
])
