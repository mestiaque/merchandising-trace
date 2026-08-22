@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('T&A Plans') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')
    @include('merchandising-trace::admin.partials.stat-card-styles')

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#eef2f9;"><i class="fa-solid fa-calendar-check" style="color:#2a4b7c;"></i></div>
                <div><div class="merch-stat-val" style="color:#2a4b7c;">{{ $stats['total'] }}</div><div class="merch-stat-lbl">Total Plans</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#ecfdf5;"><i class="fa-solid fa-flag-checkered" style="color:#10b981;"></i></div>
                <div><div class="merch-stat-val" style="color:#10b981;">{{ $stats['pcd_pass'] }}</div><div class="merch-stat-lbl">PCD Pass</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:{{ $stats['pcd_fail'] > 0 ? '#fff1f2' : '#f3f4f6' }};"><i class="fa-solid fa-triangle-exclamation" style="color:{{ $stats['pcd_fail'] > 0 ? '#f43f5e' : '#6b7280' }};"></i></div>
                <div><div class="merch-stat-val" style="color:{{ $stats['pcd_fail'] > 0 ? '#f43f5e' : '#6b7280' }};">{{ $stats['pcd_fail'] }}</div><div class="merch-stat-lbl">PCD Fail</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#fff7ed;"><i class="fa-solid fa-hourglass-half" style="color:#f59e0b;"></i></div>
                <div><div class="merch-stat-val" style="color:#f59e0b;">{{ $stats['pcd_pending'] }}</div><div class="merch-stat-lbl">PCD Pending</div></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="merch-stat-card">
                <div class="merch-stat-icon" style="background:#f5f0fb;"><i class="fa-solid fa-clock" style="color:#7c3aed;"></i></div>
                <div><div class="merch-stat-val" style="color:#7c3aed;">{{ $stats['at_risk_or_delayed'] }}</div><div class="merch-stat-lbl">At Risk / Delayed</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">T&amp;A Plans</h5>
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('merchandising-trace.tna-plans.export.excel', request()->only('buyer_id')) }}" class="btn btn-sm btn-outline-success">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>
                @can('merch_tna.edit')
                    <form method="POST" action="{{ route('merchandising-trace.tna-plans.import.excel') }}" enctype="multipart/form-data" class="d-flex gap-1">
                        @csrf
                        <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">Import Actual Dates</button>
                    </form>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="buyer_id" class="form-control merch-select2">
                        <option value="">All Buyers</option>
                        @foreach($buyersOptions as $b)
                            <option value="{{ $b->id }}" @selected(request('buyer_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="pcd_result" class="form-control merch-select2">
                        <option value="">PCD: All</option>
                        @foreach(\ME\MerchandisingTrace\Models\TnaPlan::PCD_RESULTS as $r)
                            <option value="{{ $r }}" @selected(request('pcd_result') === $r)>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="overall_status" class="form-control merch-select2">
                        <option value="">Status: All</option>
                        @foreach(\ME\MerchandisingTrace\Models\TnaPlan::OVERALL_STATUSES as $s)
                            <option value="{{ $s }}" @selected(request('overall_status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-check mt-2">
                    <input type="checkbox" name="my_orders" value="1" class="form-check-input" id="myOrders" @checked(request('my_orders')) onchange="this.form.submit()">
                    <label class="form-check-label" for="myOrders">My Orders Only</label>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('merchandising-trace.tna-plans.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr><th>TNA No</th><th>Buyer</th><th>Style</th><th>PO No</th><th>Ship Date</th><th>Completion</th><th>Status</th><th>PCD</th><th></th></tr>
                    </thead>
                    <tbody>
                        @php($ovColors = ['on_track' => 'success', 'at_risk' => 'warning', 'delayed' => 'danger', 'completed' => 'primary'])
                        @php($pcdColors = ['pending' => 'secondary', 'pass' => 'success', 'fail' => 'danger'])
                        @forelse($plans as $plan)
                            <tr>
                                <td>{{ $plan->tna_no }}</td>
                                <td>{{ $plan->salesContractPo->salesContract->buyer->name ?? '-' }}</td>
                                <td>{{ $plan->salesContractPo->style->style_no ?? '-' }}</td>
                                <td>{{ $plan->salesContractPo->po_no ?? '-' }}</td>
                                <td>{{ $plan->salesContractPo->effectiveShipment()?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $plan->completion_percent }}%</td>
                                <td><span class="badge bg-{{ $ovColors[$plan->overall_status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $plan->overall_status)) }}</span></td>
                                <td><span class="badge bg-{{ $pcdColors[$plan->pcd_result] ?? 'secondary' }}">{{ ucfirst($plan->pcd_result) }}</span></td>
                                <td class="text-end"><a href="{{ route('merchandising-trace.tna-plans.show', $plan) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">No T&amp;A plans found. Confirm a Sales Contract to generate one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $plans->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
