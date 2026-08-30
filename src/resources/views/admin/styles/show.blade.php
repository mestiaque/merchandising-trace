@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle($style->style_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $style->style_no }} — {{ $style->name }}</h5>
            <a href="{{ route('merchandising-trace.styles.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Buyer:</strong> {{ $style->buyer->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Season:</strong> {{ $style->season->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Dev Status:</strong> {{ ucfirst(str_replace('_', ' ', $style->development_status)) }}</div>
                <div class="col-md-3"><strong>SMV:</strong> {{ $style->smv }}</div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs" id="styleTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-toggle="tab" data-target="#tab-images" type="button">Images</button></li>
        <li class="nav-item"><button class="nav-link" data-toggle="tab" data-target="#tab-measurements" type="button">Measurement Chart</button></li>
        <li class="nav-item"><button class="nav-link" data-toggle="tab" data-target="#tab-parts" type="button">Parts &amp; Embellishment</button></li>
        <li class="nav-item"><button class="nav-link" data-toggle="tab" data-target="#tab-operations" type="button">Operations / SMV</button></li>
    </ul>

    <div class="tab-content border border-top-0 p-3 bg-white">
        <div class="tab-pane fade show active" id="tab-images">
            @include('merchandising-trace::admin.styles.partials.images-tab')
        </div>
        <div class="tab-pane fade" id="tab-measurements">
            @include('merchandising-trace::admin.styles.partials.measurements-tab')
        </div>
        <div class="tab-pane fade" id="tab-parts">
            @include('merchandising-trace::admin.styles.partials.parts-tab')
        </div>
        <div class="tab-pane fade" id="tab-operations">
            @include('merchandising-trace::admin.styles.partials.operations-tab')
        </div>
    </div>
</div>
@endsection
