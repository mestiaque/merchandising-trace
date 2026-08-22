@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Seasons',
    'routeBase' => 'merchandising-trace.seasons',
    'permPrefix' => 'merch_season',
    'items' => $seasons,
    'itemVar' => 'season',
    'columns' => ['code' => 'Code', 'name' => 'Name', 'year' => 'Year', 'start_date' => 'Start', 'end_date' => 'End'],
    'fieldsView' => 'merchandising-trace::admin.seasons.partials.fields',
    'modalLabel' => 'Season',
])
