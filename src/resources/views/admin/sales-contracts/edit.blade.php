@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Edit Sales Contract') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Edit Sales Contract — {{ $salesContract->contract_no }}</h4>
            <a href="{{ route('merchandising-trace.sales-contracts.show', $salesContract) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.sales-contracts.update', $salesContract) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('merchandising-trace::admin.sales-contracts.partials.form')
                <button type="submit" class="btn btn-primary mt-3 btn-sm">Update Sales Contract</button>
                <a href="{{ route('merchandising-trace.sales-contracts.show', $salesContract) }}" class="btn btn-light mt-3 btn-sm">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
