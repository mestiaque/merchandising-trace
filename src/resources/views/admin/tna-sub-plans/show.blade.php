@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Sub-T&A ' . $subPlan->sub_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $subPlan->sub_no }} — {{ ucfirst(str_replace('_', ' ', $subPlan->process_type)) }} <span class="badge badge-secondary">{{ ucfirst($subPlan->status) }}</span></h4>
            <a href="{{ route('merchandising-trace.tna-sub-plans.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3"><strong>PO:</strong> {{ $subPlan->salesContractPo->po_no ?? '-' }}</div>
                <div class="col-md-3"><strong>Style:</strong> {{ $subPlan->salesContractPo->style->style_no ?? '-' }}</div>
                <div class="col-md-3"><strong>Vendor/Plant:</strong> {{ $subPlan->vendor->name ?? $subPlan->plant_name ?? '-' }}</div>
                <div class="col-md-3"><strong>PO Qty:</strong> {{ $subPlan->po_qty }}</div>
            </div>
            <div class="row">
                <div class="col-md-3"><strong>Required PSD:</strong> {{ $subPlan->required_psd?->format('Y-m-d') ?? '-' }}</div>
                <div class="col-md-3"><strong>Required PFD:</strong> {{ $subPlan->required_pfd?->format('Y-m-d') ?? '-' }}</div>
                <div class="col-md-3"><strong>Required Qty/Day:</strong> {{ $subPlan->required_qty_per_day ?? '-' }}</div>
                <div class="col-md-3">
                    <strong>Total Sent / Received / Balance:</strong> {{ $subPlan->totalSent() }} / {{ $subPlan->totalReceived() }} / {{ $subPlan->balanceQty() }}
                    @if($subPlan->isBehindSchedule())<span class="badge badge-danger">Behind Schedule</span>@endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Daily Send / Receive Log</h6></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>Date</th><th>Sending Qty</th><th>Sending Cum</th><th>Receiving Qty</th><th>Receiving Cum</th><th>Balance</th><th>Remarks</th></tr></thead>
                <tbody>
                    @php($sendCum = 0)
                    @php($recvCum = 0)
                    @forelse($subPlan->logs as $log)
                        @php($sendCum += $log->sending_qty)
                        @php($recvCum += $log->receiving_qty)
                        <tr>
                            <td>{{ $log->log_date->format('Y-m-d') }}</td>
                            <td>{{ $log->sending_qty }}</td>
                            <td>{{ $sendCum }}</td>
                            <td>{{ $log->receiving_qty }}</td>
                            <td>{{ $recvCum }}</td>
                            <td>{{ $subPlan->po_qty - $recvCum }}</td>
                            <td>{{ $log->remarks }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No logs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h6 class="mb-0">Add / Update Today's Log</h6></div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.tna-sub-plans.logs.store', $subPlan) }}" class="row">
                @csrf
                <div class="col-md-3"><input type="date" name="log_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required></div>
                <div class="col-md-3"><input type="number" min="0" name="sending_qty" class="form-control form-control-sm" placeholder="Sending Qty" required></div>
                <div class="col-md-3"><input type="number" min="0" name="receiving_qty" class="form-control form-control-sm" placeholder="Receiving Qty" required></div>
                <div class="col-md-3"><input type="text" name="remarks" class="form-control form-control-sm" placeholder="Remarks"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100 btn-sm">Save</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
