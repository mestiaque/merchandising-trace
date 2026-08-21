@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Edit Order') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Order — {{ $order->po_number }}</h5>
            <a href="{{ route('merchandising-trace.orders.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.orders.update', $order) }}">
                @csrf @method('PUT')
                @include('merchandising-trace::admin.orders.partials.form')
                <button type="submit" class="btn btn-primary mt-3">Update Order</button>
                <a href="{{ route('merchandising-trace.orders.index') }}" class="btn btn-light mt-3">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@include('merchandising-trace::admin.partials.line-items-script')
@endsection
