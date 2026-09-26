@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Add PO Line') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Add PO Line — {{ $salesContract->contract_no }}</h4>
            <a href="{{ route('merchandising-trace.sales-contracts.show', $salesContract) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.sales-contracts.pos.store', $salesContract) }}">
                @csrf
                @include('merchandising-trace::admin.sales-contracts.pos.partials.form')
                <button type="submit" class="btn btn-primary mt-3 btn-sm">Save PO Line</button>
                <a href="{{ route('merchandising-trace.sales-contracts.show', $salesContract) }}" class="btn btn-light mt-3 btn-sm">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
