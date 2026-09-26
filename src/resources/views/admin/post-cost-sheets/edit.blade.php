@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Edit Post Cost ' . $sheet->post_cost_no) }}</title>
@endsection

@php
    $groupsMeta = \ME\MerchandisingTrace\Models\CostSheetItem::GROUPS;
    $lines = $sheet->items->groupBy('group');
    $idx = 0;
@endphp

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Post Cost {{ $sheet->post_cost_no }} — {{ $sheet->styleLabel() }} ({{ $sheet->buyer->name ?? '' }})</h4>
            <div>
                <form method="POST" action="{{ route('merchandising-trace.post-cost-sheets.refresh', $sheet) }}" class="d-inline"
                    onsubmit="return confirm('Re-pull shipped qty and booked/received material cost? Lines backed by a booking will be overwritten.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm mr-1"><i class="fa-solid fa-rotate"></i> Refresh from Bookings</button>
                </form>
                <a href="{{ route('merchandising-trace.post-cost-sheets.show', $sheet) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.post-cost-sheets.update', $sheet) }}" id="pcsForm">
                @csrf @method('PUT')

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="costing_date" class="form-control form-control-sm" value="{{ old('costing_date', optional($sheet->costing_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Order Qty <small class="text-muted">(budget, from POs)</small></label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ number_format($sheet->order_qty) }}" readonly tabindex="-1">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Shipped Qty</label>
                        <input type="number" min="0" name="shipped_qty" id="pcsShipped" class="form-control form-control-sm" value="{{ old('shipped_qty', $sheet->shipped_qty) }}">
                        <span class="form-text">0 = not shipped yet (order qty is used).</span>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Selling Price / Pc</label>
                        <input type="number" step="0.0001" min="0" name="selling_price" id="pcsPrice" class="form-control form-control-sm" value="{{ old('selling_price', $sheet->selling_price) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Actual SMV <small class="text-muted">(budget {{ $sheet->budget_smv + 0 }})</small></label>
                        <input type="number" step="0.01" min="0" name="actual_smv" class="form-control form-control-sm" value="{{ old('actual_smv', $sheet->actual_smv) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Actual CM / Dz <small class="text-muted">(budget {{ number_format($sheet->budget_cm_cost * 12, 2) }})</small></label>
                        <input type="number" step="0.0001" min="0" name="actual_cm_per_dozen" id="pcsCm" class="form-control form-control-sm" value="{{ old('actual_cm_per_dozen', round((float) $sheet->actual_cm_cost * 12, 4)) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Actual Commercial %</label>
                        <input type="number" step="0.01" min="0" max="100" name="actual_commercial_percent" id="pcsCom" class="form-control form-control-sm" value="{{ old('actual_commercial_percent', $sheet->actual_commercial_percent) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Actual Other Cost / Pc <small class="text-muted">(budget {{ number_format($sheet->budget_other_cost, 2) }})</small></label>
                        <input type="number" step="0.0001" min="0" name="actual_other_cost" id="pcsOther" class="form-control form-control-sm" value="{{ old('actual_other_cost', $sheet->actual_other_cost) }}">
                    </div>
                </div>

                @foreach($groupsMeta as $group => [$letter, $title, $totalLabel])
                    <div class="d-flex justify-content-between align-items-center mt-2 mb-1">
                        <h6 class="mb-0">{{ $letter }}. {{ $title }} <small class="text-muted">— total = cons × price{{ $group === 'fabric' ? '' : ' × 12' }}</small></h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-line="{{ $group }}"><i class="fa-solid fa-plus"></i> Add Actual Row</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-1">
                            <thead>
                                <tr>
                                    <th>Description</th><th style="width:150px">Supplier</th>
                                    <th class="text-right" style="width:85px">B. Cons</th><th class="text-right" style="width:85px">B. Price</th><th class="text-right" style="width:90px">B. Total/Dz</th>
                                    <th style="width:110px">A. Cons/Dz</th><th style="width:110px">A. Price</th><th class="text-right" style="width:90px">A. Total/Dz</th>
                                    <th class="text-right" style="width:90px">Variance</th><th style="width:36px"></th>
                                </tr>
                            </thead>
                            <tbody data-lines="{{ $group }}" data-factor="{{ \ME\MerchandisingTrace\Models\CostSheetItem::factor($group) }}">
                                @foreach($lines->get($group, collect()) as $line)
                                    @php($i = $idx++)
                                    <tr data-budget="{{ (float) $line->budget_amount }}">
                                        <td>
                                            <input type="hidden" name="lines[{{ $i }}][id]" value="{{ $line->id }}">
                                            <input type="hidden" name="lines[{{ $i }}][group]" value="{{ $group }}">
                                            <input type="text" name="lines[{{ $i }}][description]" class="form-control form-control-sm" value="{{ $line->description ?: ($line->item->name ?? '') }}">
                                            @if($line->source !== 'pre_cost')
                                                <small class="text-muted">{{ \ME\MerchandisingTrace\Models\PostCostSheetItem::SOURCES[$line->source] ?? $line->source }}</small>
                                            @endif
                                        </td>
                                        <td><input type="text" name="lines[{{ $i }}][supplier_name]" class="form-control form-control-sm" value="{{ $line->supplier_name }}"></td>
                                        <td class="text-right">{{ (float) $line->budget_consumption ? rtrim(rtrim(number_format($line->budget_consumption, 4), '0'), '.') : '-' }}</td>
                                        <td class="text-right">{{ (float) $line->budget_rate ? rtrim(rtrim(number_format($line->budget_rate, 4), '0'), '.') : '-' }}</td>
                                        <td class="text-right">{{ (float) $line->budget_amount ? number_format($line->budget_amount, 2) : '-' }}</td>
                                        <td><input type="number" step="0.0001" min="0" name="lines[{{ $i }}][actual_consumption]" class="form-control form-control-sm" value="{{ (float) $line->actual_consumption }}"></td>
                                        <td><input type="number" step="0.0001" min="0" name="lines[{{ $i }}][actual_rate]" class="form-control form-control-sm" value="{{ (float) $line->actual_rate }}"></td>
                                        <td class="text-right font-weight-bold" data-actual>-</td>
                                        <td class="text-right" data-var>-</td>
                                        <td>
                                            @if((float) $line->budget_amount == 0.0)
                                                <button type="button" class="btn-custom danger" data-remove-row title="Remove"><i class="fa-solid fa-xmark"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background:#fff4b8;font-weight:bold;">
                                    <td colspan="4" class="text-right">{{ $letter }}. {{ strtoupper($totalLabel) }}</td>
                                    <td class="text-right" data-btotal="{{ $group }}">-</td>
                                    <td colspan="2"></td>
                                    <td class="text-right" data-atotal="{{ $group }}">-</td>
                                    <td class="text-right" data-vtotal="{{ $group }}">-</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endforeach

                <div class="row mt-3">
                    <div class="col-md-6">
                        <table class="table table-bordered table-sm">
                            <thead><tr><th></th><th class="text-right">Budget / Pc</th><th class="text-right">Actual / Pc</th><th class="text-right">Variance</th></tr></thead>
                            <tbody>
                                <tr><td>Total Cost / Pc</td><td class="text-right">{{ number_format($sheet->budget_total_cost, 4) }}</td><td class="text-right font-weight-bold" id="pcsCostPc">-</td><td class="text-right" id="pcsCostVar">-</td></tr>
                                <tr><td>Profit / Pc <small class="text-muted">(selling − cost)</small></td><td class="text-right" id="pcsBProfit">-</td><td class="text-right font-weight-bold" id="pcsAProfit">-</td><td></td></tr>
                                <tr><td>Order Profit / Loss</td><td class="text-right" id="pcsBOrder">-</td><td class="text-right font-weight-bold" id="pcsAOrder">-</td><td class="text-right" id="pcsOrderVar">-</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="3">{{ old('remarks', $sheet->remarks) }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Save Post Cost</button>
                <a href="{{ route('merchandising-trace.post-cost-sheets.show', $sheet) }}" class="btn btn-light btn-sm">Cancel</a>
            </form>
        </div>
    </div>
</div>

<template id="pcsRowTpl">
    <tr data-budget="0">
        <td>
            <input type="hidden" name="lines[__I__][group]" value="__G__">
            <input type="text" name="lines[__I__][description]" class="form-control form-control-sm" placeholder="Not in pre-cost">
            <small class="text-muted">Manual</small>
        </td>
        <td><input type="text" name="lines[__I__][supplier_name]" class="form-control form-control-sm"></td>
        <td class="text-right">-</td><td class="text-right">-</td><td class="text-right">-</td>
        <td><input type="number" step="0.0001" min="0" name="lines[__I__][actual_consumption]" class="form-control form-control-sm"></td>
        <td><input type="number" step="0.0001" min="0" name="lines[__I__][actual_rate]" class="form-control form-control-sm"></td>
        <td class="text-right font-weight-bold" data-actual>-</td>
        <td class="text-right" data-var>-</td>
        <td><button type="button" class="btn-custom danger" data-remove-row title="Remove"><i class="fa-solid fa-xmark"></i></button></td>
    </tr>
</template>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('pcsForm');
        const num = function (v) { const n = parseFloat(v); return isNaN(n) ? 0 : n; };
        const fmt = function (v) { return Math.abs(v) < 0.005 ? '-' : v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
        const vfmt = function (el, v) {
            el.textContent = Math.abs(v) < 0.005 ? '-' : (v > 0 ? '+' : '−') + Math.abs(v).toFixed(2);
            el.className = el.className.replace(/\btext-(danger|success)\b/g, '').trim() + (Math.abs(v) < 0.005 ? '' : (v > 0 ? ' text-danger' : ' text-success'));
        };
        const bCmDz = {{ (float) $sheet->budget_cm_cost * 12 }};
        const bComDz = {{ (float) $sheet->budget_commercial_cost * 12 }};
        const bOtherDz = {{ (float) $sheet->budget_other_cost * 12 }};
        const orderQty = {{ (int) $sheet->order_qty }};
        let rowIndex = 100000;

        function recalc() {
            let bMat = 0, aMat = 0;
            form.querySelectorAll('[data-lines]').forEach(function (body) {
                const g = body.dataset.lines, f = num(body.dataset.factor);
                let bt = 0, at = 0;
                body.querySelectorAll('tr').forEach(function (tr) {
                    const b = num(tr.dataset.budget);
                    const a = num(tr.querySelector('[name$="[actual_consumption]"]').value) * num(tr.querySelector('[name$="[actual_rate]"]').value) * f;
                    tr.querySelector('[data-actual]').textContent = fmt(a);
                    vfmt(tr.querySelector('[data-var]'), a - b);
                    bt += b; at += a;
                });
                form.querySelector('[data-btotal="' + g + '"]').textContent = fmt(bt);
                form.querySelector('[data-atotal="' + g + '"]').textContent = fmt(at);
                vfmt(form.querySelector('[data-vtotal="' + g + '"]'), at - bt);
                bMat += bt; aMat += at;
            });
            const aCm = num(document.getElementById('pcsCm').value);
            const aCom = (aMat + aCm) * num(document.getElementById('pcsCom').value) / 100;
            const aOther = num(document.getElementById('pcsOther').value) * 12;
            const bPc = (bMat + bCmDz + bComDz + bOtherDz) / 12;
            const aPc = (aMat + aCm + aCom + aOther) / 12;
            const price = num(document.getElementById('pcsPrice').value);
            const qty = num(document.getElementById('pcsShipped').value) || orderQty;
            document.getElementById('pcsCostPc').textContent = aPc.toFixed(4);
            vfmt(document.getElementById('pcsCostVar'), aPc - bPc);
            document.getElementById('pcsBProfit').textContent = (price - bPc).toFixed(4);
            document.getElementById('pcsAProfit').textContent = (price - aPc).toFixed(4);
            document.getElementById('pcsBOrder').textContent = fmt((price - bPc) * orderQty);
            document.getElementById('pcsAOrder').textContent = fmt((price - aPc) * qty);
            vfmt(document.getElementById('pcsOrderVar'), -(((price - aPc) * qty) - ((price - bPc) * orderQty)));
        }

        form.addEventListener('input', recalc);
        form.addEventListener('click', function (e) {
            const add = e.target.closest('[data-add-line]');
            if (add) {
                const g = add.dataset.addLine;
                const html = document.getElementById('pcsRowTpl').innerHTML.replaceAll('__I__', rowIndex++).replaceAll('__G__', g);
                const wrap = document.createElement('table');
                wrap.innerHTML = '<tbody>' + html + '</tbody>';
                form.querySelector('[data-lines="' + g + '"]').appendChild(wrap.querySelector('tr'));
                recalc();
                return;
            }
            if (e.target.closest('[data-remove-row]')) {
                e.target.closest('tr').remove();
                recalc();
            }
        });
        recalc();
    });
</script>
@endpush
@endsection
