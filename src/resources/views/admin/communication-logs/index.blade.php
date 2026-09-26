@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Buyer Communication') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Log a Communication</h6></div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.communication-logs.store') }}" class="row">
                @csrf
                <div class="col-md-3"><input type="date" name="log_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required></div>
                <div class="col-md-3">
                    <select name="direction" class="form-control form-control-sm" required>
                        @foreach(\ME\MerchandisingTrace\Models\CommunicationLog::DIRECTIONS as $d)
                            <option value="{{ $d }}">{{ ucfirst($d) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="channel" class="form-control form-control-sm" required>
                        @foreach(\ME\MerchandisingTrace\Models\CommunicationLog::CHANNELS as $c)
                            <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="style_id" class="form-control form-control-sm merch-select2">
                        <option value="">— Style —</option>
                        @foreach($stylesOptions as $s)
                            <option value="{{ $s->id }}">{{ $s->style_no }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="sales_contract_po_id" class="form-control form-control-sm merch-select2">
                        <option value="">— PO —</option>
                        @foreach($posOptions as $p)
                            <option value="{{ $p->id }}">{{ $p->po_no }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"><input type="text" name="subject" class="form-control form-control-sm" placeholder="Subject" required></div>
                <div class="col-md-3"><input type="date" name="follow_up_date" class="form-control form-control-sm" placeholder="Follow-up date"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100 btn-sm">Log</button></div>
                <div class="col-md-12"><textarea name="body" class="form-control form-control-sm" rows="2" placeholder="Body / notes"></textarea></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" class="row align-items-end">
                <div class="col-md-3 mb-2"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search subject/body..." value="{{ request('search') }}"></div>
                <div class="col-md-3 mb-2 d-flex align-items-end flex-wrap gap-1">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Search</button>
                    <a href="{{ url()->current() }}" class="btn btn-light btn-sm">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>Date</th><th>Direction</th><th>Channel</th><th>Style</th><th>PO</th><th>Subject</th><th>Follow-up</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->log_date->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($log->direction) }}</td>
                            <td>{{ ucfirst($log->channel) }}</td>
                            <td>{{ $log->style->style_no ?? '-' }}</td>
                            <td>{{ $log->salesContractPo->po_no ?? '-' }}</td>
                            <td>{{ $log->subject }}</td>
                            <td>{{ optional($log->follow_up_date)->format('Y-m-d') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No communication logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $logs->links() }}</div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
