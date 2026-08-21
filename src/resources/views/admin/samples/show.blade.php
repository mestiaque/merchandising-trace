@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Sample ' . $sample->sample_number) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.ui-kit')
    @include('merchandising-trace::admin.partials.sweetalert-init')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sample — {{ $sample->sample_number }}</h5>
            <div>
                <a href="{{ route('merchandising-trace.samples.print', $sample) }}" target="_blank" class="btn btn-outline-secondary btn-sm me-1"><i class="fa-solid fa-print"></i> Print</a>
                <a href="{{ route('merchandising-trace.samples.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2"><strong>Buyer:</strong> {{ $sample->buyer->name ?? '-' }}</div>
                <div class="col-md-3 mb-2"><strong>Style:</strong> {{ $sample->style->style_no ?? '-' }} — {{ $sample->style->name ?? '' }}</div>
                <div class="col-md-3 mb-2"><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $sample->sample_type)) }}</div>
                <div class="col-md-3 mb-2"><strong>Qty:</strong> {{ $sample->qty }}</div>
                <div class="col-md-3 mb-2"><strong>Size:</strong> {{ $sample->size->name ?? '-' }}</div>
                <div class="col-md-3 mb-2"><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $sample->status)) }}</div>
                <div class="col-md-3 mb-2"><strong>Request Date:</strong> {{ optional($sample->request_date)->format('d M Y') ?? '-' }}</div>
                <div class="col-md-3 mb-2"><strong>Submission Date:</strong> {{ optional($sample->submission_date)->format('d M Y') ?? '-' }}</div>
                <div class="col-md-3 mb-2"><strong>Approval Date:</strong> {{ optional($sample->approval_date)->format('d M Y') ?? '-' }}</div>
                @if($sample->remarks)
                    <div class="col-12 mb-2"><strong>Remarks:</strong> {{ $sample->remarks }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
