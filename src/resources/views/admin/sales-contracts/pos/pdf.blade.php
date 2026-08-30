<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #222; }
        h2 { margin-bottom: 2px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; }
        .totals td { font-weight: bold; }
        .header-grid { display: table; width: 100%; margin-bottom: 10px; }
        .header-grid div { display: table-cell; width: 50%; vertical-align: top; }
        .sign-grid { display: table; width: 100%; margin-top: 40px; }
        .sign-grid div { display: table-cell; width: 50%; text-align: center; border-top: 1px solid #999; padding-top: 4px; }
    </style>
</head>
<body>
    <h2>Purchase Order — {{ $po->po_no }}</h2>
    <div class="muted">Sales Contract {{ $salesContract->contract_no }} · {{ ucfirst(str_replace('_', ' ', $po->status)) }}</div>

    <div class="header-grid">
        <div>
            <strong>Buyer:</strong> {{ $salesContract->buyer->name ?? '-' }}<br>
            <strong>Style:</strong> {{ $po->style->style_no ?? '-' }} — {{ $po->style->name ?? '' }}<br>
            <strong>Product Type:</strong> {{ $po->productType->name ?? '-' }}<br>
            <strong>Color:</strong> {{ $po->color->name ?? '-' }}<br>
            <strong>Season:</strong> {{ $salesContract->season->name ?? '-' }}
        </div>
        <div>
            <strong>PO Due Date:</strong> {{ optional($po->po_due_date)->format('d/m/Y') ?? '-' }}<br>
            <strong>PCD:</strong> {{ optional($po->effectivePcd())->format('d/m/Y') ?? '-' }}<br>
            <strong>Shipment Date:</strong> {{ optional($po->effectiveShipment())->format('d/m/Y') ?? '-' }}<br>
            <strong>Ship Mode:</strong> {{ $po->shipMode->name ?? '-' }}<br>
            <strong>Price Type:</strong> {{ $po->price_type ?? '-' }} @ {{ number_format((float) $po->unit_price, 4) }}
        </div>
    </div>

    <table>
        <thead><tr><th>Style No</th><th>Color</th>@foreach($sizes as $s)<th>{{ $s->name }}</th>@endforeach<th>Total</th></tr></thead>
        <tbody>
            <tr>
                <td>{{ $po->style->style_no ?? '-' }}</td>
                <td>{{ $po->color->name ?? '-' }}</td>
                @foreach($sizes as $s)
                    <td>{{ $po->sizes->firstWhere('size_id', $s->id)->qty ?? 0 }}</td>
                @endforeach
                <td>{{ $po->sizes->sum('qty') }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <tbody>
            <tr class="totals"><td>Order Qty (effective)</td><td>{{ $po->effectiveQty() }}</td></tr>
            <tr class="totals"><td>Unit Price</td><td>{{ number_format((float) $po->unit_price, 4) }}</td></tr>
            <tr class="totals"><td>Total Value</td><td>{{ number_format((float) $po->total_value, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="sign-grid">
        <div>Prepared By</div>
        <div>Authorized By</div>
    </div>

    <p class="muted" style="margin-top:20px;">Generated {{ now()->format('Y-m-d H:i') }}</p>
</body>
</html>
