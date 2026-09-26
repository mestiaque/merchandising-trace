{{--
    The Open Cost Sheet as printed (screen, print page and PDF). Tables only —
    dompdf has no flex/grid. props: costSheet (items.uom, items.item, style.images,
    buyer, currency loaded), forPdf (bool: embed images as data URIs).
--}}
@php
    $forPdf = $forPdf ?? false;
    $sum = $costSheet->summary();
    $cur = $costSheet->currency->symbol ?? '$';
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $groupsMeta = \ME\MerchandisingTrace\Models\CostSheetItem::GROUPS;

    $img = function (?string $path) use ($disk, $forPdf) {
        if (! $path || ! $disk->exists($path)) {
            return null;
        }

        return $forPdf
            ? 'data:' . ($disk->mimeType($path) ?: 'image/png') . ';base64,' . base64_encode($disk->get($path))
            : $disk->url($path);
    };
    $styleImages = $costSheet->style?->images?->pluck('path')->values() ?? collect();
    $front = $img($costSheet->front_image ?: $styleImages->get(0));
    $back = $img($costSheet->back_image ?: $styleImages->get(1));
    $sketch = $img($costSheet->sketch_image ?: $styleImages->get(2));
    $logo = $img(config('merchandising-trace.company.logo'));

    $money = fn ($v, int $dp = 2) => (float) $v != 0.0 ? number_format((float) $v, $dp) : '-';
    // As printed: trims prices at 4 decimals (0.0625), others at 2 (1.86) unless finer.
    $price = function ($v, string $group) {
        if ($v === null || (float) $v == 0.0) {
            return '-';
        }
        $dp = $group === 'trims' || round((float) $v, 2) != (float) $v ? 4 : 2;

        return number_format((float) $v, $dp);
    };
    $qty = fn ($v) => $v !== null && (float) $v != 0.0
        ? number_format((float) $v, round((float) $v, 2) != (float) $v ? 4 : 2)
        : '';
    $pct = fn ($v) => number_format((float) $v, 2) . ' %';
    $sl = 0;
    $lines = $costSheet->items->groupBy('group');
@endphp

<style>
    .cs-sheet { width: 100%; font-family: {!! $forPdf ? "'DejaVu Sans', sans-serif" : "Calibri, 'Segoe UI', Arial, sans-serif" !!}; font-size: {{ $forPdf ? '8px' : '12px' }}; color: #111; }
    /* Scoped resets: the print master styles every table/th/td globally. */
    .cs-sheet table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
    .cs-sheet td, .cs-sheet th { padding: {{ $forPdf ? '1px 3px' : '2px 5px' }}; vertical-align: middle; border: 0; font-size: inherit; text-align: left; }
    .cs-sheet .b td, .cs-sheet .b th { border: 1px solid #555; }
    .cs-sheet .co-name { color: #d99a00; font-size: {{ $forPdf ? '17px' : '24px' }}; font-weight: bold; letter-spacing: 1px; text-align: center; }
    .cs-sheet .co-title { color: #1f4ea0; font-weight: bold; font-size: {{ $forPdf ? '10px' : '14px' }}; text-align: center; }
    .cs-sheet .logo { width: {{ $forPdf ? '38px' : '52px' }}; height: {{ $forPdf ? '25px' : '35px' }}; padding-top: {{ $forPdf ? '13px' : '17px' }}; box-sizing: content-box; border: 2px solid #1f4ea0; border-radius: 50%; color: #1f4ea0; font-weight: bold; text-align: center; line-height: 1; font-size: {{ $forPdf ? '11px' : '15px' }}; }
    .cs-sheet .hk { font-weight: bold; width: 80px; background: #f2f2f2; }
    .cs-sheet th { background: #f2f2f2 !important; font-weight: bold; text-transform: uppercase; text-align: center; }
    .cs-sheet .r, .cs-sheet td.r { text-align: right; white-space: nowrap; }
    .cs-sheet .c, .cs-sheet td.c { text-align: center; }
    .cs-sheet td.unit { color: #1f4ea0; text-align: center; }
    .cs-sheet .tot td { background: #fff4b8; color: #1f4ea0; font-weight: bold; }
    .cs-sheet .gap td { border: 0; height: {{ $forPdf ? '5px' : '8px' }}; padding: 0; }
    .cs-sheet .sum-h { background: #f4c542; font-weight: bold; text-align: center; }
    .cs-sheet .blue { color: #1f4ea0; font-weight: bold; }
    .cs-sheet .hl { background: #ffff00 !important; font-weight: bold; }
    .cs-sheet .pics img { max-height: {{ $forPdf ? '70px' : '110px' }}; max-width: 100%; }
    .cs-sheet .sketch img { max-width: 100%; max-height: {{ $forPdf ? '150px' : '240px' }}; }
    .cs-sheet .strip th { font-size: {{ $forPdf ? '6.5px' : '10px' }}; text-transform: none; }
    .cs-sheet .strip td { text-align: center; white-space: nowrap; }
</style>

<div class="cs-sheet">
    {{-- Letterhead --}}
    <table>
        <tr>
            <td style="width:70px;">
                @if($logo)
                    <img src="{{ $logo }}" alt="" style="max-height:{{ $forPdf ? '40px' : '56px' }};">
                @else
                    <div class="logo">{{ config('merchandising-trace.company.short') }}</div>
                @endif
            </td>
            <td>
                <div class="co-name">{{ config('merchandising-trace.company.name') }}</div>
                <div class="co-title">OPEN COST SHEET</div>
            </td>
            <td style="width:70px;"></td>
        </tr>
    </table>

    {{-- Header: particulars | garment pictures | date --}}
    <table style="margin-top:4px;">
        <tr>
            <td style="width:38%; padding:0; vertical-align:top;">
                <table class="b">
                    <tr><td class="hk">BUYER</td><td>{{ $costSheet->buyer->name ?? '-' }}</td></tr>
                    <tr><td class="hk">DESCRIPTION</td><td>{{ $costSheet->garment_description ?? ($costSheet->style->name ?? '') }}</td></tr>
                    <tr><td class="hk">STYLE</td><td>{{ $costSheet->styleLabel() }}</td></tr>
                    <tr><td class="hk">SIZE</td><td>{{ $costSheet->size_range }}</td></tr>
                    <tr><td class="hk">ORDER</td><td>{{ $costSheet->order_qty ? number_format($costSheet->order_qty) . ' Pcs' : '' }}</td></tr>
                </table>
            </td>
            <td class="pics c" style="width:40%;">
                @if($front)<img src="{{ $front }}" alt="Front">@endif
                @if($back)<img src="{{ $back }}" alt="Back" style="margin-left:6px;">@endif
            </td>
            <td style="width:22%; vertical-align:top; text-align:right;">
                <strong>DATE</strong> : {{ optional($costSheet->costing_date ?? $costSheet->created_at)->format('d-M-y') }}
                <div style="margin-top:4px; color:#666;">{{ $costSheet->cost_sheet_no }} · v{{ $costSheet->version }}</div>
            </td>
        </tr>
    </table>

    {{-- A–F cost sections --}}
    <table class="b" style="margin-top:6px;">
        @foreach($groupsMeta as $group => [$letter, $title, $totalLabel])
            @if($group === 'fabric')
                <tr>
                    <th style="width:6%;">SL. No.</th><th>{{ $title }}</th><th style="width:20%;">Supplier Name</th>
                    <th style="width:10%;">Consumption</th><th style="width:8%;">Units</th><th style="width:11%;">Unit Price (YD)</th><th style="width:12%;">Total Cost ({{ $cur }})</th>
                </tr>
            @elseif($group === 'trims')
                <tr class="gap"><td colspan="7"></td></tr>
                <tr>
                    <th>SL. No.</th><th>{{ $title }}</th><th>Supplier Name</th><th>Consumption</th><th>Units</th><th>Unit Price</th><th>Total Cost DZN ({{ $cur }})</th>
                </tr>
            @else
                <tr class="gap"><td colspan="7"></td></tr>
            @endif

            @forelse($lines->get($group, collect()) as $line)
                <tr>
                    <td class="c">{{ ++$sl }}</td>
                    <td>{{ $line->label() }}</td>
                    <td>{{ $line->supplier_name }}</td>
                    <td class="r">{{ $qty($line->consumption) }}</td>
                    <td class="unit">{{ $line->uom->code ?? $line->uom->name ?? ($group === 'fabric' ? '' : 'Per Doz') }}</td>
                    <td class="r">{{ $cur }} {{ $price($line->rate, $group) }}</td>
                    <td class="r">{{ $cur }} {{ $money($line->amount) }}</td>
                </tr>
            @empty
                <tr>
                    <td class="c">{{ ++$sl }}</td>
                    <td class="c">{{ strtoupper($title) }}</td>
                    <td></td><td></td>
                    <td class="unit">{{ $group === 'fabric' ? '' : 'Per Doz' }}</td>
                    <td class="r">{{ $cur }} -</td>
                    <td class="r">{{ $cur }} -</td>
                </tr>
            @endforelse
            <tr class="tot">
                <td colspan="4" style="border:0; background:#fff;"></td>
                <td colspan="2">{{ $letter }}. {{ strtoupper($totalLabel) }}</td>
                <td class="r">{{ $cur }} {{ $money($sum['groups'][$group]['dz']) }}</td>
            </tr>
        @endforeach
    </table>

    {{-- Sketch | Summary --}}
    <table style="margin-top:8px;">
        <tr>
            <td class="sketch c" style="width:40%; vertical-align:top;">
                @if($sketch)<img src="{{ $sketch }}" alt="Sketch">@endif
            </td>
            <td style="width:60%; vertical-align:top; padding:0;">
                <table class="b">
                    <tr><td colspan="4" class="sum-h">SUMMARY</td></tr>
                    <tr><td></td><td class="c blue">DZN</td><td class="c blue">PC</td><td class="c blue">%</td></tr>
                    @foreach($groupsMeta as $group => [$letter, , $totalLabel])
                        <tr>
                            <td>{{ $letter }}. {{ strtoupper($totalLabel) }}</td>
                            <td class="r">{{ $cur }} {{ $money($sum['groups'][$group]['dz']) }}</td>
                            <td class="r">{{ $cur }} {{ $money($sum['groups'][$group]['pc']) }}</td>
                            <td class="r blue">{{ $pct($sum['groups'][$group]['pct']) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>TOTAL AMOUNT</strong></td>
                        <td class="r">{{ $cur }} {{ $money($sum['materials']['dz']) }}</td>
                        <td class="r">{{ $cur }} {{ $money($sum['materials']['pc']) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><strong>CM</strong> <span style="float:right;">SMV {{ $costSheet->smv !== null ? rtrim(rtrim(number_format((float) $costSheet->smv, 2), '0'), '.') : '-' }}</span></td>
                        <td class="r hl">{{ $cur }} {{ $money($sum['cm']['dz']) }}</td>
                        <td class="r">{{ $cur }} {{ $money($sum['cm']['pc']) }}</td>
                        <td class="r blue">{{ $pct($sum['cm']['pct']) }}</td>
                    </tr>
                    <tr>
                        <td><strong>SUB TOTAL FOB PER</strong></td>
                        <td class="r">{{ $cur }} {{ $money($sum['sub_total']['dz']) }}</td>
                        <td class="r">{{ $cur }} {{ $money($sum['sub_total']['pc']) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><strong>COMMERCIAL COST</strong> <span style="float:right;">{{ rtrim(rtrim(number_format($sum['commercial']['rate'], 2), '0'), '.') }} %</span></td>
                        <td class="r">{{ $cur }} {{ $money($sum['commercial']['dz']) }}</td>
                        <td class="r">{{ $cur }} {{ $money($sum['commercial']['pc']) }}</td>
                        <td class="r blue">{{ rtrim(rtrim(number_format($sum['commercial']['rate'], 2), '0'), '.') }} %</td>
                    </tr>
                    @if($sum['other']['dz'] > 0)
                        <tr>
                            <td><strong>OTHER COST</strong> <span style="float:right;">freight / testing / overhead</span></td>
                            <td class="r">{{ $cur }} {{ $money($sum['other']['dz']) }}</td>
                            <td class="r">{{ $cur }} {{ $money($sum['other']['pc']) }}</td>
                            <td></td>
                        </tr>
                    @endif
                    @if($sum['profit']['dz'] > 0)
                        <tr>
                            <td><strong>PROFIT</strong> <span style="float:right;">{{ rtrim(rtrim(number_format($sum['profit']['rate'], 2), '0'), '.') }} %</span></td>
                            <td class="r">{{ $cur }} {{ $money($sum['profit']['dz']) }}</td>
                            <td class="r">{{ $cur }} {{ $money($sum['profit']['pc']) }}</td>
                            <td></td>
                        </tr>
                    @endif
                    <tr>
                        <td><strong>TOTAL FOB PER DOZ</strong></td>
                        <td class="r"><strong>{{ $cur }} {{ $money($sum['fob']['dz']) }}</strong></td>
                        <td></td><td></td>
                    </tr>
                    <tr>
                        <td><strong>TOTAL FOB PER PCS</strong></td>
                        <td class="r hl">{{ $cur }} {{ $money($sum['fob']['pc']) }}</td>
                        <td class="c"><strong>TTL B2B</strong></td>
                        <td class="r blue">{{ $pct($sum['b2b_pct']) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Per-piece strip --}}
    @php($st = $sum['strip'])
    <table class="b strip" style="margin-top:8px;">
        <tr>
            <th>BUYER</th><th>STYLE</th><th>Fabric Price ({{ $st['fabric_uom'] ?: 'YD' }})</th><th>Fabric Consumption (PC)</th><th>Fabric Cost (PC)</th>
            <th>PKT</th><th>Fusing</th><th>Trims</th><th>Wash</th><th>Stone</th><th>Print</th><th>H/SEAL</th><th>CM</th><th>COM</th><th class="hl">FOB/PC</th>
        </tr>
        <tr>
            <td>{{ \Illuminate\Support\Str::limit($costSheet->buyer->name ?? '-', 18) }}</td>
            <td>{{ $costSheet->styleLabel() }}</td>
            <td>{{ $st['fabric_price'] !== null ? $cur . ' ' . $price($st['fabric_price'], 'fabric') : '-' }}</td>
            <td>{{ $st['fabric_consumption_pc'] !== null ? number_format($st['fabric_consumption_pc'], 2) . ' ' . ($st['fabric_uom'] ?: 'Yds') : '-' }}</td>
            <td>{{ $cur }} {{ $money($st['fabric_cost_pc']) }}</td>
            <td>{{ $cur }} {{ $money($st['pocket_pc']) }}</td>
            <td>{{ $cur }} {{ $money($st['fusing_pc']) }}</td>
            <td>{{ $cur }} {{ $money($sum['groups']['trims']['pc']) }}</td>
            <td>{{ $cur }} {{ $money($sum['groups']['wash']['pc']) }}</td>
            <td>{{ $cur }} {{ $money($sum['groups']['stone']['pc']) }}</td>
            <td>{{ $cur }} {{ $money($sum['groups']['print']['pc']) }}</td>
            <td>{{ $cur }} {{ $money($sum['groups']['heat_seal']['pc']) }}</td>
            <td>{{ $cur }} {{ $money($sum['cm']['pc']) }}</td>
            <td>{{ $cur }} {{ $money($sum['commercial']['pc']) }}</td>
            <td class="hl">{{ $cur }} {{ $money($sum['fob']['pc']) }}</td>
        </tr>
    </table>
</div>
