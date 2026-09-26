@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Edit PO Line') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Edit PO Line — {{ $salesContractPo->po_no }}</h4>
            <a href="{{ route('merchandising-trace.sales-contracts.show', $salesContract) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.sales-contracts.pos.update', [$salesContract, $salesContractPo]) }}">
                @csrf
                @method('PUT')
                @include('merchandising-trace::admin.sales-contracts.pos.partials.form')
                <button type="submit" class="btn btn-primary mt-3 btn-sm">Update PO Line</button>
                <a href="{{ route('merchandising-trace.sales-contracts.show', $salesContract) }}" class="btn btn-light mt-3 btn-sm">Cancel</a>
            </form>

            @if($salesContractPo->revisions->isNotEmpty())
                <hr>
                <h6>Revision History</h6>
                <table class="table table-bordered table-sm">
                    <thead><tr><th>Field</th><th>Old</th><th>New</th><th>Reason</th><th>By</th><th>When</th></tr></thead>
                    <tbody>
                        @foreach($salesContractPo->revisions as $rev)
                            <tr>
                                <td>{{ $rev->field }}</td>
                                <td>{{ $rev->old_value ?? '-' }}</td>
                                <td>{{ $rev->new_value }}</td>
                                <td>{{ $rev->reason }}</td>
                                <td>{{ $rev->changer->name ?? '-' }}</td>
                                <td>{{ $rev->changed_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
