{{-- props: inquiry (optional, for edit), buyersOptions, seasonsOptions, merchandisersOptions, factoriesOptions, productTypesOptions --}}
<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label">Inquiry Given Date <span class="text-danger">*</span></label>
        <input type="date" name="inquiry_given_date" class="form-control" value="{{ old('inquiry_given_date', optional($inquiry->inquiry_given_date ?? null)->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Buyer <span class="text-danger">*</span></label>
        <select name="buyer_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($buyersOptions as $buyer)
                <option value="{{ $buyer->id }}" @selected(old('buyer_id', $inquiry->buyer_id ?? '') == $buyer->id)>{{ $buyer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Season</label>
        <select name="season_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($seasonsOptions as $season)
                <option value="{{ $season->id }}" @selected(old('season_id', $inquiry->season_id ?? '') == $season->id)>{{ $season->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Merchant Name</label>
        <select name="merchandiser_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($merchandisersOptions as $user)
                <option value="{{ $user->id }}" @selected(old('merchandiser_id', $inquiry->merchandiser_id ?? '') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Allocated Fty</label>
        <select name="factory_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($factoriesOptions as $factory)
                <option value="{{ $factory->id }}" @selected(old('factory_id', $inquiry->factory_id ?? '') == $factory->id)>{{ $factory->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Order Confirmation Due Date</label>
        <input type="date" name="order_confirmation_due_date" class="form-control" value="{{ old('order_confirmation_due_date', optional($inquiry->order_confirmation_due_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Product Type</label>
        <select name="product_type_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($productTypesOptions as $pt)
                <option value="{{ $pt->id }}" @selected(old('product_type_id', $inquiry->product_type_id ?? '') == $pt->id)>{{ $pt->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control" id="inquiryStatus">
            @foreach(['open' => 'Open', 'quoted' => 'Quoted', 'confirmed' => 'Confirmed', 'lost' => 'Lost', 'cancelled' => 'Cancelled'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $inquiry->status ?? 'open') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Target Qty</label>
        <input type="number" min="0" name="target_qty" class="form-control" value="{{ old('target_qty', $inquiry->target_qty ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Target Price</label>
        <input type="number" step="0.0001" min="0" name="target_price" class="form-control" value="{{ old('target_price', $inquiry->target_price ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Target Ship Date</label>
        <input type="date" name="target_ship_date" class="form-control" value="{{ old('target_ship_date', optional($inquiry->target_ship_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-6 mb-3" id="lostReasonField" style="{{ old('status', $inquiry->status ?? 'open') === 'lost' ? '' : 'display:none;' }}">
        <label class="form-label">Lost Reason</label>
        <input type="text" name="lost_reason" class="form-control" value="{{ old('lost_reason', $inquiry->lost_reason ?? '') }}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $inquiry->description ?? '') }}</textarea>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $inquiry->remarks ?? '') }}</textarea>
    </div>
</div>

<hr>
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">Inquiry Items</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addInquiryItemBtn"><i class="fa-solid fa-plus"></i> Add Row</button>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle">
        <thead>
            <tr><th>Style Ref</th><th>Product Type</th><th>Color Ref</th><th style="width:120px">Qty</th><th style="width:140px">Target Price</th><th>Remarks</th><th style="width:40px"></th></tr>
        </thead>
        <tbody id="inqRowsBody">
            @php $lines = old('items', isset($inquiry) ? $inquiry->items->map(fn ($i) => $i->toArray())->all() : [[]]); @endphp
            @foreach($lines as $index => $line)
                <tr>
                    <td><input type="text" name="items[{{ $index }}][style_ref]" class="form-control" value="{{ $line['style_ref'] ?? '' }}"></td>
                    <td>
                        <select name="items[{{ $index }}][product_type_id]" class="form-control merch-select2">
                            <option value="">— Select —</option>
                            @foreach($productTypesOptions as $pt)
                                <option value="{{ $pt->id }}" @selected(($line['product_type_id'] ?? null) == $pt->id)>{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="items[{{ $index }}][color_ref]" class="form-control" value="{{ $line['color_ref'] ?? '' }}"></td>
                    <td><input type="number" min="0" name="items[{{ $index }}][qty]" class="form-control" value="{{ $line['qty'] ?? '' }}"></td>
                    <td><input type="number" step="0.0001" min="0" name="items[{{ $index }}][target_price]" class="form-control" value="{{ $line['target_price'] ?? '' }}"></td>
                    <td><input type="text" name="items[{{ $index }}][remarks]" class="form-control" value="{{ $line['remarks'] ?? '' }}"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row><i class="fa-solid fa-xmark"></i></button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<template id="inqRowTemplate">
    <tr>
        <td><input type="text" name="items[__INDEX__][style_ref]" class="form-control"></td>
        <td>
            <select name="items[__INDEX__][product_type_id]" class="form-control merch-select2">
                <option value="">— Select —</option>
                @foreach($productTypesOptions as $pt)
                    <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="items[__INDEX__][color_ref]" class="form-control"></td>
        <td><input type="number" min="0" name="items[__INDEX__][qty]" class="form-control"></td>
        <td><input type="number" step="0.0001" min="0" name="items[__INDEX__][target_price]" class="form-control"></td>
        <td><input type="text" name="items[__INDEX__][remarks]" class="form-control"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row><i class="fa-solid fa-xmark"></i></button></td>
    </tr>
</template>

@push('js')
<script>
    (function () {
        let rowIndex = 1000000;
        document.getElementById('inquiryStatus')?.addEventListener('change', function () {
            document.getElementById('lostReasonField').style.display = this.value === 'lost' ? '' : 'none';
        });
        document.getElementById('addInquiryItemBtn')?.addEventListener('click', function () {
            const tpl = document.getElementById('inqRowTemplate');
            const body = document.getElementById('inqRowsBody');
            const html = tpl.innerHTML.replaceAll('__INDEX__', rowIndex++);
            const wrap = document.createElement('table');
            wrap.innerHTML = '<tbody>' + html + '</tbody>';
            body.appendChild(wrap.querySelector('tr'));
            prodSelect2Init(document);
        });
        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-remove-row]')) {
                e.target.closest('tr').remove();
            }
        });
    })();
</script>
@endpush
