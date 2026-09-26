@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Reports') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.ui-kit')
    <div class="card">
        <div class="card-header"><h4 class="mb-0">Reports</h4></div>
        <div class="list-group list-group-flush">
            @foreach($reports as $key => $label)
                <a href="{{ route('merchandising-trace.reports.show', $key) }}" class="list-group-item list-group-item-action">{{ $label }}</a>
            @endforeach
        </div>
    </div>
</div>
@endsection
