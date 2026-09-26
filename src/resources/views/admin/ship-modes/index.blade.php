@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Ship Modes',
    'routeBase' => 'merchandising-trace.ship-modes',
    'permPrefix' => 'merch_ship_mode',
    'items' => $shipModes,
    'itemVar' => 'record',
    'columns' => ['code' => 'Code', 'name' => 'Name'],
    'fieldsView' => 'merchandising-trace::admin.partials.code-name-fields',
    'fieldsExtra' => ['idPrefix' => 'shipMode'],
    'modalLabel' => 'Ship Mode',
])



