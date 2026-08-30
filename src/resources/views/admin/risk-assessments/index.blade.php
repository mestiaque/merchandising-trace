@include('merchandising-trace::admin.partials.simple-master-index', [
    'title' => 'Risk Assessment',
    'routeBase' => 'merchandising-trace.risk-assessments',
    'permPrefix' => 'merch_risk_assessment',
    'items' => $riskAssessments,
    'itemVar' => 'riskAssessment',
    'columns' => ['style.style_no' => 'Style No', 'style.name' => 'Style Name', 'season.name' => 'Season', 'category' => 'Category'],
    'fieldsView' => 'merchandising-trace::admin.risk-assessments.partials.fields',
    'fieldsExtra' => [
        'stylesOptions' => $stylesOptions, 'seasonsOptions' => $seasonsOptions, 'factoriesOptions' => $factoriesOptions,
    ],
    'modalLabel' => 'Risk Assessment',
])
