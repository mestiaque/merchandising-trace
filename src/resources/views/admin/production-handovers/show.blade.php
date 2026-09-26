@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Handover — ' . $po->po_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $po->po_no }} — {{ $po->style->style_no ?? '-' }}</h4>
            <a href="{{ route('merchandising-trace.production-handovers.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Buyer:</strong> {{ $po->salesContract->buyer->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Effective Qty:</strong> {{ $po->effectiveQty() }}</div>
                <div class="col-md-3"><strong>PCD:</strong> {{ optional($po->effectivePcd())->format('Y-m-d') ?? '-' }}</div>
                <div class="col-md-3"><strong>Status:</strong> <span class="badge badge-secondary">{{ ucfirst(str_replace('_',' ',$po->status)) }}</span></div>
            </div>
        </div>
    </div>

    @if(!$po->style->trc_product_id || !$po->style->trc_size_group_id)
        <div class="card mb-3 border-warning">
            <div class="card-header alert-warning"><h6 class="mb-0">One-time setup: map this style to Production</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('merchandising-trace.styles.map-production', $po->style) }}" class="row">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label">Production Product</label>
                        <select name="trc_product_id" class="form-control form-control-sm" required>
                            <option value="">— Select —</option>
                            @foreach($trcProducts as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Size Group</label>
                        <select name="trc_size_group_id" class="form-control form-control-sm" required>
                            <option value="">— Select —</option>
                            @foreach($trcSizeGroups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100 btn-sm">Save</button></div>
                </form>
            </div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Pre-flight Checklist</h6></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>Check</th><th>Result</th></tr></thead>
                <tbody>
                    @foreach($checks as $key => $c)
                        @continue($key === 'all_passed')
                        <tr>
                            <td>{{ $c['label'] }}</td>
                            <td>
                                @if($c['pass'])
                                    <span class="badge badge-success">Pass</span>
                                @else
                                    <span class="badge badge-danger">Fail</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($po->production_plan_line_id)
        <div class="alert alert-info">Already handed over — plan line #{{ $po->production_plan_line_id }} in production-trace.</div>
        @can('merch_production_handover.edit')
            <form method="POST" action="{{ route('merchandising-trace.production-handovers.rollback', $po) }}">
                @csrf
                <div class="mb-2"><input type="text" name="reason" class="form-control form-control-sm" placeholder="Rollback reason" required></div>
                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Roll back this handover? Only allowed if no cutting has started.')">Rollback Handover</button>
            </form>
        @endcan
    @elseif($po->style->trc_product_id && $po->style->trc_size_group_id)
        @can('merch_production_handover.edit')
            <form method="POST" action="{{ route('merchandising-trace.production-handovers.push', $po) }}">
                @csrf
                @if(!$checks['all_passed'])
                    <div class="mb-2">
                        <label class="form-label text-danger">Override reason (required — checklist has failing items)</label>
                        <input type="text" name="override_reason" class="form-control form-control-sm" required>
                    </div>
                @endif
                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Push this PO to production?')">
                    {{ $checks['all_passed'] ? 'Handover to Production' : 'Override & Handover to Production' }}
                </button>
            </form>
        @endcan
    @endif
</div>
@endsection
