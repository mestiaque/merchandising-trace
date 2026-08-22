@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('T&A Alerts') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">T&amp;A Alerts</h5>
            <a href="{{ route('merchandising-trace.tna-alerts.index', ['unread_only' => request('unread_only') ? null : 1]) }}" class="btn btn-outline-secondary btn-sm">
                {{ request('unread_only') ? 'Show All' : 'Unread Only' }}
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead><tr><th>Date</th><th>Type</th><th>Task</th><th>PO</th><th></th></tr></thead>
                <tbody>
                    @php($typeColors = ['due_soon' => 'warning', 'overdue' => 'danger', 'blocked_pcd' => 'dark'])
                    @forelse($alerts as $alert)
                        <tr class="{{ $alert->is_read ? '' : 'fw-bold' }}">
                            <td>{{ $alert->alert_date->format('Y-m-d') }}</td>
                            <td><span class="badge bg-{{ $typeColors[$alert->alert_type] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $alert->alert_type)) }}</span></td>
                            <td>{{ $alert->task->task_name ?? '-' }}</td>
                            <td>
                                @if($alert->task?->plan)
                                    <a href="{{ route('merchandising-trace.tna-plans.show', $alert->task->plan) }}">{{ $alert->task->plan->salesContractPo->po_no ?? $alert->task->plan->tna_no }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                @unless($alert->is_read)
                                    <form method="POST" action="{{ route('merchandising-trace.tna-alerts.mark-read', $alert) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Mark Read</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No alerts.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $alerts->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
