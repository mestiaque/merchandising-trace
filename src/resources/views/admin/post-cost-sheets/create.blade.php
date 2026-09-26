@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('New Post Cost Sheet') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">New Post Cost Sheet</h4>
            <a href="{{ route('merchandising-trace.post-cost-sheets.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Pick the approved pre-cost (Open Cost Sheet). Its lines become the <strong>budget</strong>; the <strong>actual</strong> side is
                filled from the style's material bookings / receipts, POs and shipped quantity — you only correct what differs.
            </p>
            <form method="POST" action="{{ route('merchandising-trace.post-cost-sheets.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Pre-cost Sheet <span class="text-danger">*</span></label>
                        <select name="cost_sheet_id" class="form-control form-control-sm merch-select2" required>
                            <option value="">— Select approved pre-cost —</option>
                            @foreach($preCostsOptions as $cs)
                                <option value="{{ $cs->id }}" @selected(old('cost_sheet_id', $selectedPreCost) == $cs->id)>
                                    {{ $cs->cost_sheet_no }} — {{ $cs->styleLabel() }} ({{ $cs->buyer->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @if($preCostsOptions->isEmpty())
                            <span class="form-text text-danger">No approved pre-cost sheet yet — approve one under Costing (Pre-order) first.</span>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Sales Contract</label>
                        <select name="sales_contract_id" class="form-control form-control-sm merch-select2">
                            <option value="">— All contracts of the style —</option>
                            @foreach($contractsOptions as $sc)
                                <option value="{{ $sc->id }}" @selected(old('sales_contract_id') == $sc->id)>{{ $sc->contract_no }}{{ $sc->lc_no ? ' / LC ' . $sc->lc_no : '' }} — {{ $sc->buyer->name ?? '' }}</option>
                            @endforeach
                        </select>
                        <span class="form-text">Limit POs, bookings and shipments to one contract.</span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Create &amp; Fill Actuals</button>
                <a href="{{ route('merchandising-trace.post-cost-sheets.index') }}" class="btn btn-light btn-sm">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
