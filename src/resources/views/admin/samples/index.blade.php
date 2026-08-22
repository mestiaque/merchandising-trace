@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Samples') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="row g-2 mb-3">
        @php($statusColors = ['requested' => 'secondary', 'in_progress' => 'warning', 'submitted' => 'info', 'approved' => 'success', 'rejected' => 'danger', 'resubmit' => 'dark', 'cancelled' => 'secondary'])
        @foreach(\ME\MerchandisingTrace\Models\Sample::STATUSES as $status)
            <div class="col">
                <a href="{{ route('merchandising-trace.samples.index', ['status' => $status]) }}" class="card text-decoration-none h-100 {{ request('status') === $status ? 'border-primary' : '' }}">
                    <div class="card-body text-center p-2">
                        <div class="fs-4 fw-bold text-{{ $statusColors[$status] ?? 'secondary' }}">{{ $boardCounts[$status] ?? 0 }}</div>
                        <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $status)) }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Samples</h5>
            @can('merch_sample.add')
                <a href="{{ route('merchandising-trace.samples.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add Sample</a>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="buyer_id" class="form-control merch-select2">
                        <option value="">All Buyers</option>
                        @foreach($buyersOptions as $buyer)
                            <option value="{{ $buyer->id }}" @selected(request('buyer_id') == $buyer->id)>{{ $buyer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sample_type_id" class="form-control merch-select2">
                        <option value="">All Types</option>
                        @foreach($sampleTypesOptions as $type)
                            <option value="{{ $type->id }}" @selected(request('sample_type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control merch-select2">
                        <option value="">All Status</option>
                        @foreach(\ME\MerchandisingTrace\Models\Sample::STATUSES as $value)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ ucfirst(str_replace('_', ' ', $value)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('merchandising-trace.samples.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr><th>#</th><th>Sample No</th><th>Buyer</th><th>Style</th><th>Type</th><th>Qty</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($samples as $sample)
                            <tr>
                                <td>{{ $loop->iteration + $samples->firstItem() - 1 }}</td>
                                <td>{{ $sample->sample_no }} @if($sample->revision_no > 1)<span class="badge bg-dark">rev {{ $sample->revision_no }}</span>@endif</td>
                                <td>{{ $sample->buyer->name ?? '-' }}</td>
                                <td>{{ $sample->style->name ?? '-' }}</td>
                                <td>{{ $sample->sampleType->name ?? '-' }}</td>
                                <td>{{ $sample->qty }}</td>
                                <td><span class="badge p-1 text-white bg-{{ $statusColors[$sample->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $sample->status)) }}</span></td>
                                <td class="text-end">
                                    @can('merch_sample.view')
                                        <a href="{{ route('merchandising-trace.samples.show', $sample) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                                    @endcan
                                    @can('merch_sample.edit')
                                        <a href="{{ route('merchandising-trace.samples.edit', $sample) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                    @endcan
                                    @can('merch_sample.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal"
                                            data-target="#deleteSampleModal" data-action="{{ route('merchandising-trace.samples.destroy', $sample) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No samples found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $samples->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteSampleModal', 'label' => 'samples'])
@include('merchandising-trace::admin.partials.select2-init')
@endsection
