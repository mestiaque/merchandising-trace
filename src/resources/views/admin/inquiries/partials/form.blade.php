{{-- props: inquiry (optional, for edit), buyersOptions, seasonsOptions, merchandisersOptions, factoriesOptions, productTypesOptions
     One inquiry = one item: style ref / color / qty / price live on the inquiry itself. --}}
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
        <label class="form-label">Style Ref</label>
        <input type="text" name="style_ref" class="form-control" maxlength="150" value="{{ old('style_ref', $inquiry->style_ref ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Color</label>
        <input type="text" name="color_ref" class="form-control" maxlength="150" value="{{ old('color_ref', $inquiry->color_ref ?? '') }}">
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label">Order Qty</label>
        <input type="number" min="0" name="target_qty" id="inqOrderQty" class="form-control" value="{{ old('target_qty', $inquiry->target_qty ?? '') }}">
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label">Unit Price</label>
        <input type="number" step="0.0001" min="0" name="target_price" id="inqUnitPrice" class="form-control" value="{{ old('target_price', $inquiry->target_price ?? '') }}">
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label">Total Value</label>
        <input type="text" id="inqTotalValue" class="form-control bg-light" readonly tabindex="-1">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Ship Date</label>
        <input type="date" name="target_ship_date" class="form-control" value="{{ old('target_ship_date', optional($inquiry->target_ship_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Extended Ship Date</label>
        <input type="date" name="extended_ship_date" class="form-control" value="{{ old('extended_ship_date', optional($inquiry->extended_ship_date ?? null)->format('Y-m-d')) }}">
        <span class="form-text">Only if the buyer extended the original ship date.</span>
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

@push('js')
<script>
    (function () {
        document.getElementById('inquiryStatus')?.addEventListener('change', function () {
            document.getElementById('lostReasonField').style.display = this.value === 'lost' ? '' : 'none';
        });

        const qty = document.getElementById('inqOrderQty');
        const price = document.getElementById('inqUnitPrice');
        const total = document.getElementById('inqTotalValue');
        function recalc() {
            const q = parseFloat(qty.value), p = parseFloat(price.value);
            total.value = (isNaN(q) || isNaN(p)) ? '' : (q * p).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        qty.addEventListener('input', recalc);
        price.addEventListener('input', recalc);
        recalc();
    })();
</script>
@endpush
