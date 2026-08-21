@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Order ' . $order->po_number) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.ui-kit')
    @include('merchandising-trace::admin.partials.sweetalert-init')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Order — {{ $order->po_number }}</h5>
            <div>
                <a href="{{ route('merchandising-trace.orders.print', $order) }}" target="_blank" class="btn btn-outline-secondary btn-sm me-1"><i class="fa-solid fa-print"></i> Print</a>
                <a href="{{ route('merchandising-trace.orders.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Buyer:</strong> {{ $order->buyer->name ?? '-' }}</div>
                <div class="col-md-3"><strong>Style:</strong> {{ $order->style->style_no ?? '-' }} — {{ $order->style->name ?? '' }}</div>
                <div class="col-md-3"><strong>Order Qty:</strong> {{ number_format($order->order_qty) }}</div>
                <div class="col-md-3"><strong>Delivery Date:</strong> {{ optional($order->delivery_date)->format('d M Y') ?? '-' }}</div>
                <div class="col-md-3 mt-2"><strong>Price:</strong> {{ $order->price !== null ? number_format($order->price, 2) . ' ' . $order->currency : '-' }}</div>
                <div class="col-md-3 mt-2"><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $order->status)) }}</div>
                <div class="col-md-3 mt-2"><strong>BOM:</strong> {{ $bom ? 'v' . $bom->version . ' (' . $bom->status . ')' : 'Not created yet' }}</div>
                @if($order->description)
                    <div class="col-12 mt-2"><strong>Description:</strong> {{ $order->description }}</div>
                @endif
            </div>

            <hr>
            <h6>Color / Size Breakdown</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead><tr><th>Color</th><th>Size</th><th>Qty</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->color->name ?? '-' }}</td>
                                <td>{{ $item->size->name ?? '-' }}</td>
                                <td>{{ $item->qty }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
