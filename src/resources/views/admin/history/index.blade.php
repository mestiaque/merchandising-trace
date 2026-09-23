@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('360° History') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><i class="fa-solid fa-timeline"></i> 360° History</h5>
            <p class="text-muted small mb-0 mt-1">Search by PO No, Style No/Name, or Buyer to see everything that happened — Merchandising, Production and Inventory — in one timeline.</p>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-8">
                    <input type="text" name="q" class="form-control" placeholder="PO no, style no/name, or buyer name…" value="{{ $q }}" autofocus>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search"></i> Search</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.history.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if($q !== '')
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header"><strong>POs</strong> <span class="text-muted small">({{ $pos->count() }})</span></div>
                    <div class="list-group list-group-flush">
                        @forelse($pos as $po)
                            <a href="{{ route('merchandising-trace.history.po', $po) }}" class="list-group-item list-group-item-action">
                                <div class="fw-bold">{{ $po->po_no }}</div>
                                <div class="small text-muted">{{ $po->style->style_no ?? '-' }} — {{ $po->salesContract->buyer->name ?? '-' }}</div>
                            </a>
                        @empty
                            <div class="list-group-item text-muted small">No matching POs.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header"><strong>Styles</strong> <span class="text-muted small">({{ $styles->count() }})</span></div>
                    <div class="list-group list-group-flush">
                        @forelse($styles as $style)
                            <a href="{{ route('merchandising-trace.history.style', $style) }}" class="list-group-item list-group-item-action">
                                <div class="fw-bold">{{ $style->style_no }}</div>
                                <div class="small text-muted">{{ $style->name }} — {{ $style->buyer->name ?? '-' }}</div>
                            </a>
                        @empty
                            <div class="list-group-item text-muted small">No matching styles.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header"><strong>Buyers</strong> <span class="text-muted small">({{ $buyers->count() }})</span></div>
                    <div class="list-group list-group-flush">
                        @forelse($buyers as $buyer)
                            <a href="{{ route('merchandising-trace.styles.index', ['search' => $buyer->name]) }}" class="list-group-item list-group-item-action">
                                <div class="fw-bold">{{ $buyer->name }}</div>
                                <div class="small text-muted">Pick a style or PO of theirs above for its full timeline</div>
                            </a>
                        @empty
                            <div class="list-group-item text-muted small">No matching buyers.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
