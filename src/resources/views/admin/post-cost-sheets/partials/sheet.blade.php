{{--
    Post Cost Sheet (budget vs actual), read-only — show page and print page.
    props: sheet (items.uom, items.item, buyer, style, currency, costSheet, salesContract loaded)
--}}
@php
    $sum = $sheet->summary();
    $cur = $sheet->currency->symbol ?? '$';
    $groupsMeta = \ME\MerchandisingTrace\Models\CostSheetItem::GROUPS;
    $lines = $sheet->items->groupBy('group');
    $m = fn ($v) => (float) $v != 0.0 ? number_format((float) $v, 2) : '-';
    $p = fn ($v) => (float) $v != 0.0 ? rtrim(rtrim(number_format((float) $v, 4), '0'), '.') : '-';
    // Over budget = red, under = green (for costs); for profit the sign flips.
    $vcls = fn ($v, $good = 'lower') => abs((float) $v) < 0.005 ? '' : ((($v > 0) xor ($good === 'higher')) ? 'pcs-bad' : 'pcs-good');
    $vtxt = fn ($v) => abs((float) $v) < 0.005 ? '-' : (($v > 0 ? '+' : '−') . number_format(abs((float) $v), 2));
    $pct = fn ($v) => $v === null ? '' : (($v > 0 ? '+' : '') . number_format($v, 1) . '%');
    $sl = 0;
@endphp

<style>
    .pcs-sheet { width: 100%; font-family: Calibri, 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #111; }
    /* Scoped resets: the print master styles every table/th/td globally. */
    .pcs-sheet table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
    .pcs-sheet td, .pcs-sheet th { padding: 2px 5px; border: 1px solid #555; font-size: inherit; text-align: left; vertical-align: middle; }
    .pcs-sheet th { background: #f2f2f2 !important; color: #000 !important; font-weight: bold; text-align: center; text-transform: uppercase; }
    .pcs-sheet table.nb, .pcs-sheet table.nb > tbody > tr > td, .pcs-sheet table.nb > tr > td { border: 0; }
    .pcs-sheet .co-name { color: #d99a00; font-size: 24px; font-weight: bold; letter-spacing: 1px; text-align: center; }
    .pcs-sheet .co-title { color: #1f4ea0; font-weight: bold; font-size: 14px; text-align: center; }
    .pcs-sheet td.r, .pcs-sheet th.r { text-align: right; white-space: nowrap; }
    .pcs-sheet td.c { text-align: center; }
    .pcs-sheet .hk { font-weight: bold; background: #f2f2f2; width: 110px; }
    .pcs-sheet th.bud { background: #e8eefb !important; }
    .pcs-sheet th.act { background: #fff4d6 !important; }
    .pcs-sheet .tot td { background: #fff4b8; color: #1f4ea0; font-weight: bold; }
    .pcs-sheet .gap td { border: 0; height: 8px; padding: 0; }
    .pcs-sheet .sec td { background: #fafafa; font-weight: bold; }
    .pcs-sheet .pcs-bad { color: #c62828; font-weight: bold; }
    .pcs-sheet .pcs-good { color: #2e7d32; font-weight: bold; }
    .pcs-sheet .grand td { background: #ffff00 !important; font-weight: bold; }
    .pcs-sheet .sum-h { background: #f4c542 !important; font-weight: bold; text-align: center; }
    .pcs-sheet .src { font-size: 10px; color: #777; }
</style>

<div class="pcs-sheet">
    <table class="nb">
        <tr>
            <td>
                <div class="co-name">{{ config('merchandising-trace.company.name') }}</div>
                <div class="co-title">POST COST SHEET <span style="color:#777;font-weight:normal;">(Budget vs Actual)</span></div>
            </td>
        </tr>
    </table>

    <table style="margin-top:6px;">
        <tr>
            <td class="hk">BUYER</td><td>{{ $sheet->buyer->name ?? '-' }}</td>
            <td class="hk">STYLE</td><td>{{ $sheet->styleLabel() }}</td>
            <td class="hk">PRE-COST</td><td>{{ $sheet->costSheet->cost_sheet_no ?? '-' }}</td>
            <td class="hk">DATE</td><td>{{ optional($sheet->costing_date)->format('d-M-y') }}</td>
        </tr>
        <tr>
            <td class="hk">DESCRIPTION</td><td>{{ $sheet->garment_description }}</td>
            <td class="hk">CONTRACT</td><td>{{ $sheet->salesContract->contract_no ?? 'All contracts' }}</td>
            <td class="hk">ORDER QTY</td><td>{{ number_format($sheet->order_qty) }} Pcs</td>
            <td class="hk">SHIPPED QTY</td><td>{{ $sheet->shipped_qty ? number_format($sheet->shipped_qty) . ' Pcs' : 'Not shipped' }}</td>
        </tr>
    </table>

    {{-- A–F: budget vs actual per line (per dozen) --}}
    <table style="margin-top:8px;">
        <tr>
            <th rowspan="2" style="width:4%;">SL</th>
            <th rowspan="2">Description</th>
            <th rowspan="2" style="width:13%;">Supplier</th>
            <th rowspan="2" style="width:5%;">Unit</th>
            <th colspan="3" class="bud">Budget (Pre-cost)</th>
            <th colspan="3" class="act">Actual</th>
            <th rowspan="2" style="width:8%;">Variance / Dz</th>
        </tr>
        <tr>
            <th class="bud">Cons/Dz</th><th class="bud">Price</th><th class="bud">Total/Dz</th>
            <th class="act">Cons/Dz</th><th class="act">Price</th><th class="act">Total/Dz</th>
        </tr>
        @foreach($groupsMeta as $group => [$letter, $title, $totalLabel])
            <tr class="sec"><td colspan="11">{{ $letter }}. {{ strtoupper($title) }}</td></tr>
            @forelse($lines->get($group, collect()) as $line)
                <tr>
                    <td class="c">{{ ++$sl }}</td>
                    <td>{{ $line->label() }} @if($line->source !== 'pre_cost')<span class="src">({{ \ME\MerchandisingTrace\Models\PostCostSheetItem::SOURCES[$line->source] ?? $line->source }})</span>@endif</td>
                    <td>{{ $line->supplier_name }}</td>
                    <td class="c">{{ $line->uom->code ?? $line->uom->name ?? '' }}</td>
                    <td class="r">{{ $p($line->budget_consumption) }}</td>
                    <td class="r">{{ $p($line->budget_rate) }}</td>
                    <td class="r">{{ $m($line->budget_amount) }}</td>
                    <td class="r">{{ $p($line->actual_consumption) }}</td>
                    <td class="r">{{ $p($line->actual_rate) }}</td>
                    <td class="r">{{ $m($line->actual_amount) }}</td>
                    <td class="r {{ $vcls($line->variance()) }}">{{ $vtxt($line->variance()) }}</td>
                </tr>
            @empty
                <tr><td class="c">{{ ++$sl }}</td><td colspan="10" style="color:#999;">—</td></tr>
            @endforelse
            <tr class="tot">
                <td colspan="4" style="border:0;background:#fff;"></td>
                <td colspan="2">{{ $letter }}. {{ strtoupper($totalLabel) }}</td>
                <td class="r">{{ $cur }} {{ $m($sum['groups'][$group]['b']['dz']) }}</td>
                <td colspan="2"></td>
                <td class="r">{{ $cur }} {{ $m($sum['groups'][$group]['a']['dz']) }}</td>
                <td class="r {{ $vcls($sum['groups'][$group]['v']['dz']) }}">{{ $vtxt($sum['groups'][$group]['v']['dz']) }}</td>
            </tr>
        @endforeach
    </table>

    <table class="nb" style="margin-top:10px;">
        <tr>
            {{-- Cost summary --}}
            <td style="width:58%; vertical-align:top; padding:0 6px 0 0;">
                <table>
                    <tr><td colspan="6" class="sum-h">COST SUMMARY</td></tr>
                    <tr><th></th><th class="bud">Budget / Dz</th><th class="act">Actual / Dz</th><th>Variance / Dz</th><th>Var %</th><th class="act">Actual / Pc</th></tr>
                    @foreach($groupsMeta as $group => [$letter, , $totalLabel])
                        @php($r = $sum['groups'][$group])
                        <tr>
                            <td>{{ $letter }}. {{ strtoupper($totalLabel) }}</td>
                            <td class="r">{{ $m($r['b']['dz']) }}</td><td class="r">{{ $m($r['a']['dz']) }}</td>
                            <td class="r {{ $vcls($r['v']['dz']) }}">{{ $vtxt($r['v']['dz']) }}</td>
                            <td class="r {{ $vcls($r['v']['dz']) }}">{{ $pct($r['v']['pct']) }}</td>
                            <td class="r">{{ $m($r['a']['pc']) }}</td>
                        </tr>
                    @endforeach
                    @foreach([
                        'materials' => 'TOTAL MATERIALS',
                        'cm' => 'CM (SMV ' . ($sheet->budget_smv + 0) . ' → ' . ($sheet->actual_smv + 0) . ')',
                        'commercial' => 'COMMERCIAL (' . ($sheet->actual_commercial_percent + 0) . '% actual)',
                        'other' => 'OTHER COST',
                    ] as $key => $label)
                        @continue($key === 'other' && ! $sum['other']['b']['dz'] && ! $sum['other']['a']['dz'])
                        @php($r = $sum[$key])
                        <tr style="{{ $key === 'materials' ? 'font-weight:bold;' : '' }}">
                            <td>{{ $label }}</td>
                            <td class="r">{{ $m($r['b']['dz']) }}</td><td class="r">{{ $m($r['a']['dz']) }}</td>
                            <td class="r {{ $vcls($r['v']['dz']) }}">{{ $vtxt($r['v']['dz']) }}</td>
                            <td class="r {{ $vcls($r['v']['dz']) }}">{{ $pct($r['v']['pct']) }}</td>
                            <td class="r">{{ $m($r['a']['pc']) }}</td>
                        </tr>
                    @endforeach
                    @php($r = $sum['cost'])
                    <tr class="grand">
                        <td>TOTAL COST</td>
                        <td class="r">{{ $cur }} {{ $m($r['b']['dz']) }}</td><td class="r">{{ $cur }} {{ $m($r['a']['dz']) }}</td>
                        <td class="r {{ $vcls($r['v']['dz']) }}">{{ $vtxt($r['v']['dz']) }}</td>
                        <td class="r {{ $vcls($r['v']['dz']) }}">{{ $pct($r['v']['pct']) }}</td>
                        <td class="r">{{ $cur }} {{ $m($r['a']['pc']) }}</td>
                    </tr>
                    <tr>
                        <td>TOTAL COST / PC</td>
                        <td class="r">{{ $cur }} {{ $m($r['b']['pc']) }}</td><td class="r">{{ $cur }} {{ $m($r['a']['pc']) }}</td>
                        <td class="r {{ $vcls($r['v']['pc']) }}">{{ $vtxt($r['v']['pc']) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </table>
            </td>

            {{-- Order profit & loss --}}
            <td style="width:42%; vertical-align:top; padding:0;">
                @php($o = $sum['order'])
                <table>
                    <tr><td colspan="4" class="sum-h">ORDER PROFIT &amp; LOSS</td></tr>
                    <tr><th></th><th class="bud">Budget</th><th class="act">Actual</th><th>Variance</th></tr>
                    <tr>
                        <td>Quantity (pcs)</td>
                        <td class="r">{{ number_format($o['order_qty']) }}</td>
                        <td class="r">{{ number_format($o['actual_qty']) }}@unless($o['shipped'])<span class="src"> (order)</span>@endunless</td>
                        <td class="r">{{ $vtxt($o['actual_qty'] - $o['order_qty']) }}</td>
                    </tr>
                    <tr>
                        <td>Selling price / pc</td>
                        <td class="r" colspan="2" style="text-align:center;">{{ $cur }} {{ $p($o['selling_price']) }}</td><td></td>
                    </tr>
                    <tr>
                        <td>Revenue</td>
                        <td class="r">{{ $m($o['budget']['revenue']) }}</td><td class="r">{{ $m($o['actual']['revenue']) }}</td>
                        <td class="r {{ $vcls($o['actual']['revenue'] - $o['budget']['revenue'], 'higher') }}">{{ $vtxt($o['actual']['revenue'] - $o['budget']['revenue']) }}</td>
                    </tr>
                    <tr>
                        <td>Total cost</td>
                        <td class="r">{{ $m($o['budget']['total_cost']) }}</td><td class="r">{{ $m($o['actual']['total_cost']) }}</td>
                        <td class="r {{ $vcls($o['actual']['total_cost'] - $o['budget']['total_cost']) }}">{{ $vtxt($o['actual']['total_cost'] - $o['budget']['total_cost']) }}</td>
                    </tr>
                    <tr class="grand">
                        <td>PROFIT / LOSS</td>
                        <td class="r">{{ $cur }} {{ $vtxt($o['budget']['profit']) }}</td>
                        <td class="r">{{ $cur }} {{ $vtxt($o['actual']['profit']) }}</td>
                        <td class="r {{ $vcls($o['actual']['profit'] - $o['budget']['profit'], 'higher') }}">{{ $vtxt($o['actual']['profit'] - $o['budget']['profit']) }}</td>
                    </tr>
                    <tr>
                        <td>Margin</td>
                        <td class="r">{{ $o['budget']['margin'] === null ? '-' : number_format($o['budget']['margin'], 2) . '%' }}</td>
                        <td class="r {{ $o['actual']['margin'] !== null && $o['actual']['margin'] < 0 ? 'pcs-bad' : '' }}">{{ $o['actual']['margin'] === null ? '-' : number_format($o['actual']['margin'], 2) . '%' }}</td>
                        <td></td>
                    </tr>
                </table>
                @if($sheet->remarks)
                    <div style="margin-top:6px;"><strong>Remarks:</strong> @richtext($sheet->remarks)</div>
                @endif
            </td>
        </tr>
    </table>
</div>
