@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Production Reports') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Production Reports</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($reports as $report)
                    <div class="col-md-4 col-lg-3">
                        <a href="{{ route($report['route']) }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="fa-solid {{ $report['icon'] }} fa-lg mb-2"></i>
                            {{ $report['label'] }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
