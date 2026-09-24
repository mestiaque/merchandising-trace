@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle($style->style_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')

    @php
        $devStatusColors = [
            'new' => 'secondary', 'in_development' => 'info', 'sample_stage' => 'warning',
            'approved' => 'primary', 'in_production' => 'success', 'closed' => 'dark',
        ];
        $devStatusColor = $devStatusColors[$style->development_status] ?? 'secondary';
    @endphp

    <div class="card mb-3 style-header-card">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0">{{ $style->style_no }} <span class="text-muted">—</span> {{ $style->name }}</h5>
                <span class="badge bg-{{ $devStatusColor }}">{{ ucfirst(str_replace('_', ' ', $style->development_status)) }}</span>
            </div>
            <a href="{{ route('merchandising-trace.styles.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <div class="style-meta-grid">
                <div class="style-meta-item">
                    <div class="style-meta-label"><i class="fa-solid fa-file-invoice"></i> PO No</div>
                    <div class="style-meta-value">{{ $style->po_no ?? '—' }}</div>
                </div>
                <div class="style-meta-item">
                    <div class="style-meta-label"><i class="fa-solid fa-building"></i> Buyer</div>
                    <div class="style-meta-value">{{ $style->buyer->name ?? '—' }}</div>
                </div>
                <div class="style-meta-item">
                    <div class="style-meta-label"><i class="fa-solid fa-sun"></i> Season</div>
                    <div class="style-meta-value">{{ $style->season->name ?? '—' }}</div>
                </div>
                <div class="style-meta-item">
                    <div class="style-meta-label"><i class="fa-solid fa-stopwatch"></i> SMV</div>
                    <div class="style-meta-value">{{ $style->smv ?? '—' }}</div>
                </div>
                <div class="style-meta-item">
                    <div class="style-meta-label"><i class="fa-solid fa-handshake"></i> Confirm CM / Dz</div>
                    <div class="style-meta-value">{{ $style->confirm_cm !== null ? number_format((float) $style->confirm_cm, 2) : '—' }}</div>
                </div>
                <div class="style-meta-item">
                    <div class="style-meta-label"><i class="fa-solid fa-magnifying-glass-dollar"></i> Inquiry</div>
                    <div class="style-meta-value">
                        @if($style->inquiry)
                            <a href="{{ route('merchandising-trace.inquiries.show', $style->inquiry) }}">{{ $style->inquiry->inquiry_no }}</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs" id="styleTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-toggle="tab" data-target="#tab-tech-pack" type="button"><i class="fa-solid fa-file-pdf me-1"></i> Tech Pack</button></li>
        {{-- <li class="nav-item"><button class="nav-link" data-toggle="tab" data-target="#tab-images" type="button">Images</button></li>
        <li class="nav-item"><button class="nav-link" data-toggle="tab" data-target="#tab-measurements" type="button">Measurement Chart</button></li>
        <li class="nav-item"><button class="nav-link" data-toggle="tab" data-target="#tab-parts" type="button">Parts &amp; Embellishment</button></li>
        <li class="nav-item"><button class="nav-link" data-toggle="tab" data-target="#tab-operations" type="button">Operations / SMV</button></li> --}}
    </ul>

    <div class="tab-content border border-top-0 p-3 bg-white">
        <div class="tab-pane fade show active" id="tab-tech-pack">
            @if($style->tech_pack_file)
                {{-- <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small"><i class="fa-solid fa-circle-info"></i> Buyer-provided tech pack</span>
                    <a href="{{ route('merchandising-trace.styles.tech-pack.download', $style) }}" class="btn btn-sm btn-primary"><i class="fa-solid fa-download"></i> Download</a>
                </div> --}}
                <div class="tech-pack-viewer border rounded">
                    <iframe src="{{ route('merchandising-trace.styles.tech-pack.view', $style) }}" title="Tech Pack PDF"></iframe>
                </div>
            @else
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-file-pdf fa-2x mb-2 d-block"></i>
                    No tech pack uploaded yet. Upload the buyer's PDF via Edit Style.
                </div>
            @endif
        </div>
        <div class="tab-pane fade" id="tab-images">
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

<style>
    .style-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
    }
    .style-meta-label {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #8a8f98;
        margin-bottom: .15rem;
    }
    .style-meta-label i { width: 1rem; }
    .style-meta-value {
        font-size: 1rem;
        font-weight: 600;
        color: #2b2f36;
    }
    .tech-pack-viewer {
        width: 100%;
        height: calc(100vh - 260px);
        min-height: 600px;
        overflow: hidden;
    }
    .tech-pack-viewer iframe {
        width: 100%;
        height: 100%;
        border: 0;
        display: block;
    }
</style>
@endsection
