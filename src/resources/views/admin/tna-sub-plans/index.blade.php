@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Sub-T&A Plans') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sub-T&amp;A (Embroidery / Print / After-Wash)</h5>
            @can('merch_tna.add')
                <a href="{{ route('merchandising-trace.tna-sub-plans.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Sub-T&amp;A</a>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead><tr><th>Sub No</th><th>PO</th><th>Style</th><th>Process</th><th>Sent/Received/Balance</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($subPlans as $sp)
                        <tr>
                            <td>{{ $sp->sub_no }}</td>
                            <td>{{ $sp->salesContractPo->po_no ?? '-' }}</td>
                            <td>{{ $sp->salesContractPo->style->style_no ?? '-' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $sp->process_type)) }}</td>
                            <td>{{ $sp->totalSent() }} / {{ $sp->totalReceived() }} / {{ $sp->balanceQty() }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($sp->status) }}</span></td>
                            <td class="text-end"><a href="{{ route('merchandising-trace.tna-sub-plans.show', $sp) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No sub-T&amp;A plans found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $subPlans->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
