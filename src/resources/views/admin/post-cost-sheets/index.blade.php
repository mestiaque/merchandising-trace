@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Post Cost Sheets') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Post Cost Sheets</h4>
            @can('merch_post_costing.add')
                <a href="{{ route('merchandising-trace.post-cost-sheets.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Post Cost Sheet</a>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search post cost no / style" value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        @foreach(\ME\MerchandisingTrace\Models\PostCostSheet::STATUSES as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 d-flex align-items-end flex-wrap gap-1">
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    <a href="{{ route('merchandising-trace.post-cost-sheets.index') }}" class="btn btn-light btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>#</th><th>Post Cost No</th><th>Pre-cost</th><th>Style</th><th>Buyer</th><th>Contract</th>
                            <th class="text-right">Budget / Pc</th><th class="text-right">Actual / Pc</th><th class="text-right">Variance / Pc</th>
                            <th>Status</th><th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sheets as $s)
                            @php($var = (float) $s->actual_total_cost - (float) $s->budget_total_cost)
                            <tr>
                                <td>{{ $loop->iteration + $sheets->firstItem() - 1 }}</td>
                                <td>{{ $s->post_cost_no }}</td>
                                <td>{{ $s->costSheet->cost_sheet_no ?? '-' }}</td>
                                <td>{{ $s->styleLabel() }}</td>
                                <td>{{ $s->buyer->name ?? '-' }}</td>
                                <td>{{ $s->salesContract->contract_no ?? 'All' }}</td>
                                <td class="text-right">{{ number_format((float) $s->budget_total_cost, 2) }}</td>
                                <td class="text-right">{{ number_format((float) $s->actual_total_cost, 2) }}</td>
                                <td class="text-right {{ abs($var) < 0.005 ? '' : ($var > 0 ? 'text-danger' : 'text-success') }}">{{ abs($var) < 0.005 ? '-' : ($var > 0 ? '+' : '−') . number_format(abs($var), 2) }}</td>
                                <td><span class="badge badge-{{ $s->status === 'approved' ? 'success' : 'secondary' }}">{{ ucfirst($s->status) }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('merchandising-trace.post-cost-sheets.show', $s) }}" class="btn-custom success" title="View"><i class="fa-solid fa-eye"></i></a>
                                    @can('merch_post_costing.edit')
                                        @if($s->status !== 'approved')
                                            <a href="{{ route('merchandising-trace.post-cost-sheets.edit', $s) }}" class="btn-custom yellow" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                        @endif
                                    @endcan
                                    @can('merch_post_costing.delete')
                                        <button type="button" class="btn-custom danger" data-toggle="modal" data-target="#deletePostCostModal"
                                            data-action="{{ route('merchandising-trace.post-cost-sheets.destroy', $s) }}" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center text-muted">No post cost sheets yet. Create one from an approved pre-cost sheet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sheets->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deletePostCostModal', 'label' => 'post cost sheets'])
@endsection
