@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Add Material Booking') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add Material Booking</h5>
            <a href="{{ route('merchandising-trace.material-bookings.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.material-bookings.store') }}">
                @csrf

                <h6 class="text-muted text-uppercase small mb-3">Booking Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" id="bookingType" class="form-control" required>
                            @foreach(\ME\MerchandisingTrace\Models\MaterialBooking::TYPES as $t)
                                <option value="{{ $t }}" @selected(request('type') === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-9 mb-3">
                        @php($against = old('booking_against', request('booking_against', 'sales_contract')))
                        <label class="form-label d-block">Booking Against <span class="text-danger">*</span></label>
                        <div class="btn-group merch-btn-radio" role="group">
                            @foreach(\ME\MerchandisingTrace\Models\MaterialBooking::BOOKING_AGAINST as $value => $label)
                                <input type="radio" name="booking_against" id="against_{{ $value }}" value="{{ $value }}" @checked($against === $value)>
                                <label class="btn btn-outline-primary {{ $against === $value ? 'active' : '' }}" for="against_{{ $value }}">{{ $label }}</label>
                            @endforeach
                        </div>
                    </div>
                    @php($selectedContract = old('sales_contract_id', request('sales_contract_id')))
                    <div class="col-md-5 mb-3" data-against-panel="sales_contract">
                        <label class="form-label">Sales Contract <span class="text-danger">*</span></label>
                        <select name="sales_contract_id" class="form-control merch-select2" data-contract-select required>
                            <option value="">— Select —</option>
                            @foreach($salesContractsOptions as $sc)
                                <option value="{{ $sc->id }}" @selected($selectedContract == $sc->id)>{{ $sc->contract_no }} — {{ $sc->buyer->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 mb-3" data-against-panel="lc">
                        <label class="form-label">LC <span class="text-danger">*</span></label>
                        <select name="sales_contract_id" class="form-control merch-select2" data-contract-select required>
                            <option value="">— Select —</option>
                            @foreach($salesContractsOptions->filter(fn ($sc) => filled($sc->lc_no)) as $sc)
                                <option value="{{ $sc->id }}" @selected($selectedContract == $sc->id)>LC {{ $sc->lc_no }} — {{ $sc->contract_no }} ({{ $sc->buyer->name ?? '' }})</option>
                            @endforeach
                        </select>
                        <span class="form-text">Only contracts with an LC number are listed.</span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Style <span class="text-danger">*</span></label>
                        <select name="style_id" id="bookingStyle" class="form-control merch-select2" required>
                            <option value="">— Select —</option>
                            @foreach($stylesOptions as $s)
                                <option value="{{ $s->id }}" @selected(old('style_id') == $s->id)>{{ $s->style_no }} — {{ $s->name }}</option>
                            @endforeach
                        </select>
                        <span class="form-text" id="bookingStyleHint"></span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-control merch-select2">
                            <option value="">— Select —</option>
                            @foreach($suppliersOptions as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mill / Country</label>
                        <input type="text" name="mill_country" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Currency</label>
                        <select name="currency_id" class="form-control merch-select2">
                            <option value="">— Select —</option>
                            @foreach($currenciesOptions as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-muted text-uppercase small mb-0">Items to Book</h6>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="bookingLoadBomBtn" title="Fill items from the style's BOM × the order qty on the selected contract">
                            <i class="fa-solid fa-file-import"></i> Load from BOM
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addBookingItemBtn"><i class="fa-solid fa-plus"></i> Add Row</button>
                    </div>
                </div>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm align-middle">
                        <thead><tr><th style="min-width:200px">Item</th><th style="min-width:140px">Color</th><th style="width:130px">Booked Qty</th><th style="min-width:120px">UOM</th><th style="width:110px">Rate</th><th style="width:40px"></th></tr></thead>
                        <tbody id="bookingItemsBody">
                            <tr>
                                <td>
                                    <select name="items[0][item_id]" class="form-control merch-select2">
                                        <option value="">— Select —</option>
                                        @foreach($itemsOptions as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][color_id]" class="form-control merch-select2">
                                        <option value="">—</option>
                                        @foreach($colorsOptions as $color)
                                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" step="0.0001" min="0" name="items[0][booked_qty]" class="form-control"></td>
                                <td>
                                    <select name="items[0][uom_id]" class="form-control merch-select2">
                                        <option value="">—</option>
                                        @foreach($uomsOptions as $uom)
                                            <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" step="0.0001" min="0" name="items[0][rate]" class="form-control"></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <template id="bookingItemRowTemplate">
                    <tr>
                        <td>
                            <select name="items[__INDEX__][item_id]" class="form-control merch-select2">
                                <option value="">— Select —</option>
                                @foreach($itemsOptions as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="items[__INDEX__][color_id]" class="form-control merch-select2">
                                <option value="">—</option>
                                @foreach($colorsOptions as $color)
                                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" step="0.0001" min="0" name="items[__INDEX__][booked_qty]" class="form-control"></td>
                        <td>
                            <select name="items[__INDEX__][uom_id]" class="form-control merch-select2">
                                <option value="">—</option>
                                @foreach($uomsOptions as $uom)
                                    <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" step="0.0001" min="0" name="items[__INDEX__][rate]" class="form-control"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row><i class="fa-solid fa-xmark"></i></button></td>
                    </tr>
                </template>

                <button type="submit" class="btn btn-primary">Save Booking</button>
                <a href="{{ route('merchandising-trace.material-bookings.index') }}" class="btn btn-light">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@push('css')
<style>
    .merch-btn-radio input[type="radio"] { position: absolute; clip: rect(0, 0, 0, 0); pointer-events: none; }
</style>
@endpush
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let rowIndex = 1000000;
        const body = document.getElementById('bookingItemsBody');
        const styleSel = document.getElementById('bookingStyle');
        const allStyleOptions = Array.from(styleSel.options).map(function (o) { return o.cloneNode(true); });
        const hint = document.getElementById('bookingStyleHint');
        const jq = typeof $ !== 'undefined' ? $ : null;
        let contractStyles = null; // [{id, order_qty}] of the chosen contract

        function addRow(data) {
            const tpl = document.getElementById('bookingItemRowTemplate');
            const wrap = document.createElement('table');
            wrap.innerHTML = '<tbody>' + tpl.innerHTML.replaceAll('__INDEX__', rowIndex++) + '</tbody>';
            const row = wrap.querySelector('tr');
            Object.entries(data || {}).forEach(function ([key, value]) {
                const el = row.querySelector('[name$="[' + key + ']"]');
                if (el && value !== null && value !== undefined) { el.value = value; }
            });
            body.appendChild(row);
            prodSelect2Init(document);
        }
        document.getElementById('addBookingItemBtn')?.addEventListener('click', function () { addRow(); });
        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-remove-row]')) {
                e.target.closest('tr').remove();
            }
        });

        // Sales Contract vs LC — only the active picker is enabled (and submitted).
        function activePicker() {
            return document.querySelector('[data-against-panel]:not([style*="none"]) [data-contract-select]');
        }
        function setAgainst(value) {
            document.querySelectorAll('[data-against-panel]').forEach(function (panel) {
                const on = panel.dataset.againstPanel === value;
                panel.style.display = on ? '' : 'none';
                panel.querySelector('select').disabled = !on;
            });
            document.querySelectorAll('input[name="booking_against"]').forEach(function (r) {
                document.querySelector('label[for="' + r.id + '"]').classList.toggle('active', r.checked);
            });
            loadContract();
        }

        // Picking the contract narrows the style list to the styles on it.
        function loadContract() {
            const picker = activePicker();
            const id = picker ? picker.value : '';
            contractStyles = null;
            hint.textContent = '';
            if (!id) { restoreStyles(null); return; }
            fetch('{{ route('merchandising-trace.lookup.sales-contract', '__ID__') }}'.replace('__ID__', id), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
                .then(function (data) {
                    contractStyles = data.styles || [];
                    restoreStyles(contractStyles.map(function (s) { return String(s.id); }));
                    if (contractStyles.length === 1) {
                        styleSel.value = String(contractStyles[0].id);
                    }
                    if (jq) { jq(styleSel).trigger('change'); }
                    hint.textContent = contractStyles.length ? '' : 'No PO on this contract yet — showing all styles.';
                })
                .catch(function () { restoreStyles(null); });
        }
        function restoreStyles(allowedIds) {
            const current = styleSel.value;
            styleSel.innerHTML = '';
            allStyleOptions.forEach(function (o) {
                if (!o.value || !allowedIds || !allowedIds.length || allowedIds.indexOf(o.value) !== -1) {
                    styleSel.appendChild(o.cloneNode(true));
                }
            });
            styleSel.value = Array.from(styleSel.options).some(function (o) { return o.value === current; }) ? current : '';
            if (jq) { jq(styleSel).trigger('change.select2'); }
        }

        document.querySelectorAll('input[name="booking_against"]').forEach(function (r) {
            r.addEventListener('change', function () { setAgainst(this.value); });
        });
        if (jq) {
            jq(document).on('change', '[data-contract-select]', loadContract);
        }
        setAgainst(document.querySelector('input[name="booking_against"]:checked')?.value || 'sales_contract');

        // Load from BOM: qty = BOM net consumption per piece × order qty of the style on this contract.
        const typeMap = { fabric: 'fabric', trims: 'trim', accessory: 'accessory', packing: 'packing' };
        document.getElementById('bookingLoadBomBtn')?.addEventListener('click', function () {
            if (!styleSel.value) { alert('Select a style first.'); return; }
            const btn = this;
            btn.disabled = true;
            fetch('{{ route('merchandising-trace.lookup.style', '__ID__') }}'.replace('__ID__', styleSel.value), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
                .then(function (data) {
                    const itemType = typeMap[document.getElementById('bookingType').value];
                    const lines = ((data.bom && data.bom.lines) || []).filter(function (l) { return l.item_type === itemType; });
                    if (!lines.length) { alert('No ' + document.getElementById('bookingType').value + ' lines on a built BOM for this style.'); return; }
                    const onContract = (contractStyles || []).find(function (s) { return String(s.id) === styleSel.value; });
                    const qty = onContract ? onContract.order_qty : 0;
                    body.querySelectorAll('tr').forEach(function (row) {
                        if (!row.querySelector('[name$="[item_id]"]').value) { row.remove(); }
                    });
                    lines.forEach(function (l) {
                        addRow({
                            item_id: l.item_id, color_id: l.color_id, uom_id: l.uom_id, rate: l.rate,
                            booked_qty: qty ? (l.net_consumption * qty).toFixed(4) : '',
                        });
                    });
                    if (!qty) { alert('Order qty for this style on the contract is unknown — enter booked qty manually.'); }
                })
                .catch(function () { alert('Could not load the BOM for this style.'); })
                .finally(function () { btn.disabled = false; });
        });
    });
</script>
@endpush
@endsection
