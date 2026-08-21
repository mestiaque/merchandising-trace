@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Samples') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.ui-kit')
    @include('merchandising-trace::admin.partials.sweetalert-init')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Samples</h5>
            <div class="d-flex align-items-center">
                <a href="{{ route('merchandising-trace.samples.print-list', request()->query()) }}" target="_blank" class="btn btn-outline-secondary btn-sm me-1"><i class="fa-solid fa-print"></i> Print</a>
                <span class="dt-excel-slot"></span>
                @can('merch_sample.add')
                <a href="{{ route('merchandising-trace.samples.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Add Sample
                </a>
            @endcan
            </div>
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
                    <select name="sample_type" class="form-control merch-select2">
                        <option value="">All Types</option>
                        @foreach(\ME\MerchandisingTrace\Models\Sample::SAMPLE_TYPES as $type)
                            <option value="{{ $type }}" @selected(request('sample_type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control merch-select2">
                        <option value="">All Status</option>
                        @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'sent' => 'Sent to Buyer', 'approved' => 'Approved', 'rejected' => 'Rejected', 'revise' => 'Revise Requested'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
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
                <table class="table table-bordered table-striped align-middle" id="merchDataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Sample Number</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($samples as $sample)
                            <tr>
                                <td>{{ $loop->iteration + $samples->firstItem() - 1 }}</td>
                                <td>{{ $sample->sample_number }}</td>
                                <td>{{ $sample->buyer->name ?? '-' }}</td>
                                <td>{{ $sample->style->name ?? '-' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $sample->sample_type)) }}</td>
                                <td>{{ $sample->qty }}</td>
                                <td>
                                    @php($statusColors = ['pending' => 'secondary', 'in_progress' => 'warning', 'sent' => 'info', 'approved' => 'success', 'rejected' => 'danger', 'revise' => 'dark'])
                                    <span class="badge p-1 text-white bg-{{ $statusColors[$sample->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $sample->status)) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @can('merch_sample.view')
                                        <a href="{{ route('merchandising-trace.samples.show', $sample) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                                    @endcan
                                    @can('merch_sample.edit')
                                        <a href="{{ route('merchandising-trace.samples.edit', $sample) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                    @endcan
                                    @can('merch_sample.delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-confirm-delete data-confirm-label="Sample"
                                            data-action="{{ route('merchandising-trace.samples.destroy', $sample) }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No samples found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $samples->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@include('merchandising-trace::admin.partials.datatable-init', ['tableId' => 'merchDataTable'])
@endsection
