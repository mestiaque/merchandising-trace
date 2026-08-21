@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Add Order') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add Order</h5>
            <a href="{{ route('merchandising-trace.orders.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.orders.store') }}">
                @csrf
                @include('merchandising-trace::admin.orders.partials.form')
                <button type="submit" class="btn btn-primary mt-3">Save Order</button>
                <a href="{{ route('merchandising-trace.orders.index') }}" class="btn btn-light mt-3">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@include('merchandising-trace::admin.partials.line-items-script')
@endsection
