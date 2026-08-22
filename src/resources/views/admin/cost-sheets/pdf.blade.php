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
    </style>
</head>
<body>
    <h2>Cost Sheet — {{ $costSheet->cost_sheet_no }}</h2>
    <div class="muted">Version {{ $costSheet->version }} · {{ ucfirst($costSheet->status) }}</div>

    <div class="header-grid">
        <div>
            <strong>Buyer:</strong> {{ $costSheet->buyer->name ?? '-' }}<br>
            <strong>Style:</strong> {{ $costSheet->style->style_no ?? '-' }} — {{ $costSheet->style->name ?? '' }}<br>
            <strong>Order Qty:</strong> {{ $costSheet->order_qty }}<br>
            <strong>Price Type:</strong> {{ $costSheet->price_type }}
        </div>
        <div>
            <strong>SMV:</strong> {{ $costSheet->smv }}<br>
            <strong>Efficiency:</strong> {{ $costSheet->efficiency_percent }}%<br>
            <strong>CM/min rate:</strong> {{ $costSheet->cm_minute_rate }}<br>
            <strong>Currency:</strong> {{ $costSheet->currency->code ?? '-' }}
        </div>
    </div>

    <table>
        <thead><tr><th>Cost Component</th><th>Amount</th></tr></thead>
        <tbody>
            <tr><td>Fabric Cost</td><td>{{ number_format((float) $costSheet->fabric_cost, 4) }}</td></tr>
            <tr><td>Trims Cost</td><td>{{ number_format((float) $costSheet->trims_cost, 4) }}</td></tr>
            <tr><td>Accessories Cost</td><td>{{ number_format((float) $costSheet->accessories_cost, 4) }}</td></tr>
            <tr><td>Print/Emb Cost</td><td>{{ number_format((float) $costSheet->print_emb_cost, 4) }}</td></tr>
            <tr><td>Wash Cost</td><td>{{ number_format((float) $costSheet->wash_cost, 4) }}</td></tr>
            <tr><td>CM (computed)</td><td>{{ number_format($costSheet->calcCm(), 4) }}</td></tr>
            <tr><td>Commercial Cost</td><td>{{ number_format((float) $costSheet->commercial_cost, 4) }}</td></tr>
            <tr><td>Freight Cost</td><td>{{ number_format((float) $costSheet->freight_cost, 4) }}</td></tr>
            <tr><td>Testing Cost</td><td>{{ number_format((float) $costSheet->testing_cost, 4) }}</td></tr>
            <tr><td>Overhead Cost</td><td>{{ number_format((float) $costSheet->overhead_cost, 4) }}</td></tr>
            <tr class="totals"><td>Total Cost</td><td>{{ number_format($costSheet->calcTotalCost(), 4) }}</td></tr>
            <tr class="totals"><td>Offer Price ({{ $costSheet->profit_percent }}% profit)</td><td>{{ number_format($costSheet->calcOfferPrice(), 4) }}</td></tr>
            <tr class="totals"><td>Margin %</td><td>{{ $costSheet->calcMarginPercent() }}%</td></tr>
        </tbody>
    </table>

    @if($costSheet->items->isNotEmpty())
        <h3>Line Items</h3>
        <table>
            <thead><tr><th>Group</th><th>Item</th><th>Description</th><th>Consumption</th><th>Rate</th><th>Amount</th></tr></thead>
            <tbody>
                @foreach($costSheet->items as $line)
                    <tr>
                        <td>{{ ucfirst($line->group) }}</td>
                        <td>{{ $line->item->name ?? '-' }}</td>
                        <td>{{ $line->description }}</td>
                        <td>{{ $line->consumption }}</td>
                        <td>{{ $line->rate }}</td>
                        <td>{{ $line->amount }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="muted" style="margin-top:20px;">Generated {{ now()->format('Y-m-d H:i') }}</p>
</body>
</html>
