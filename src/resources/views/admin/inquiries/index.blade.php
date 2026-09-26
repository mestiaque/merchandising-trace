@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Inquiries') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Inquiries</h4>
            @can('merch_inquiry.add')
                <a href="{{ route('merchandising-trace.inquiries.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Inquiry</a>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search inquiry no / style ref" value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control form-control-sm merch-select2">
                        <option value="">All Status</option>
                        @foreach(['open' => 'Open', 'quoted' => 'Quoted', 'confirmed' => 'Confirmed', 'lost' => 'Lost', 'cancelled' => 'Cancelled'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 d-flex align-items-end flex-wrap gap-1">
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    <a href="{{ route('merchandising-trace.inquiries.index') }}" class="btn btn-light btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>#</th><th>Inquiry No</th><th>Buyer</th><th>Style Ref</th><th class="text-right">Order Qty</th><th class="text-right">Total Value</th><th>Merchant</th><th>Given Date</th><th>Confirmation Due</th><th>Status</th><th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inquiries as $inquiry)
                            <tr>
                                <td>{{ $loop->iteration + $inquiries->firstItem() - 1 }}</td>
                                <td>{{ $inquiry->inquiry_no }}</td>
                                <td>{{ $inquiry->buyer->name ?? '-' }}</td>
                                <td>{{ $inquiry->style_ref ?? '-' }}</td>
                                <td class="text-right">{{ $inquiry->target_qty !== null ? number_format($inquiry->target_qty) : '-' }}</td>
                                <td class="text-right">{{ $inquiry->total_value !== null ? number_format((float) $inquiry->total_value, 2) : '-' }}</td>
                                <td>{{ $inquiry->merchandiser->name ?? '-' }}</td>
                                <td>{{ $inquiry->inquiry_given_date?->format('Y-m-d') }}</td>
                                <td>
                                    {{ $inquiry->order_confirmation_due_date?->format('Y-m-d') ?? '-' }}
                                    @if($inquiry->isOverdue())
                                        <span class="badge badge-warning">Overdue</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ ['open' => 'primary', 'quoted' => 'info', 'confirmed' => 'success', 'lost' => 'danger', 'cancelled' => 'secondary'][$inquiry->status] ?? 'secondary' }}">{{ ucfirst($inquiry->status) }}</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('merchandising-trace.inquiries.show', $inquiry) }}" class="btn-custom success"><i class="fa-solid fa-eye"></i></a>
                                    @can('merch_inquiry.edit')
                                        <a href="{{ route('merchandising-trace.inquiries.edit', $inquiry) }}" class="btn-custom yellow"><i class="fa-solid fa-pen"></i></a>
                                    @endcan
                                    @can('merch_inquiry.delete')
                                        <button type="button" class="btn-custom danger" data-toggle="modal"
                                            data-target="#deleteInquiryModal" data-action="{{ route('merchandising-trace.inquiries.destroy', $inquiry) }}"><i class="fa-solid fa-trash"></i></button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center text-muted">No inquiries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $inquiries->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.delete-confirm-modal', ['modalId' => 'deleteInquiryModal', 'label' => 'inquiries'])
@include('merchandising-trace::admin.partials.select2-init')
@endsection
