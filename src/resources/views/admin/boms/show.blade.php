@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('BOM ' . $bom->bom_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                BOM {{ $bom->bom_no }} <span class="badge bg-secondary">v{{ $bom->version }}</span>
                @php($statusColors = ['draft' => 'secondary', 'submitted' => 'info', 'approved' => 'success', 'revised' => 'dark'])
                <span class="badge bg-{{ $statusColors[$bom->status] ?? 'secondary' }} ms-1">{{ ucfirst($bom->status) }}</span>
            </h5>
            <div>
                @can('merch_bom.edit')
                    @if($bom->status !== 'approved')
                        <a href="{{ route('merchandising-trace.boms.edit', $bom) }}" class="btn btn-outline-primary btn-sm me-1"><i class="fa-solid fa-pen"></i> Edit</a>
                        <form method="POST" action="{{ route('merchandising-trace.boms.approve', $bom) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm me-1"><i class="fa-solid fa-check"></i> Approve</button>
                        </form>
                    @endif
                @endcan
                @if($bom->bom_file)
                    <a href="{{ route('merchandising-trace.boms.file.download', $bom) }}" class="btn btn-outline-primary btn-sm me-1"><i class="fa-solid fa-download"></i> Download</a>
                @endif
                <a href="{{ route('merchandising-trace.boms.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Style:</strong> {{ $bom->style->style_no ?? '-' }} — {{ $bom->style->name ?? '' }}</div>
                <div class="col-md-4"><strong>Buyer:</strong> {{ $bom->style->buyer->name ?? '-' }}</div>
                <div class="col-md-4"><strong>Approved By:</strong> {{ $bom->approver->name ?? '-' }}</div>
            </div>
            @if($bom->remarks)
                <div class="mt-2"><strong>Remarks:</strong> {{ $bom->remarks }}</div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h6 class="mb-0">BOM Document</h6></div>
        <div class="card-body">
            @if($bom->bom_file)
                <div class="bom-viewer border rounded">
                    <iframe src="{{ route('merchandising-trace.boms.file.view', $bom) }}" title="BOM PDF"></iframe>
                </div>
            @else
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-file-pdf fa-2x mb-2 d-block"></i>
                    No BOM uploaded yet. Upload the buyer's PDF via Edit BOM.
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .bom-viewer {
        width: 100%;
        height: calc(100vh - 260px);
        min-height: 600px;
        overflow: hidden;
    }
    .bom-viewer iframe {
        width: 100%;
        height: 100%;
        border: 0;
        display: block;
    }
</style>
@endsection
