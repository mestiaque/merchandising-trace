{{-- props: order (optional, for edit), buyersOptions, stylesOptions, colorsOptions, sizesOptions --}}
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Buyer <span class="text-danger">*</span></label>
        <select name="buyer_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($buyersOptions as $buyer)
                <option value="{{ $buyer->id }}" @selected(old('buyer_id', $order->buyer_id ?? '') == $buyer->id)>{{ $buyer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Style <span class="text-danger">*</span></label>
        <select name="style_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($stylesOptions as $style)
                <option value="{{ $style->id }}" @selected(old('style_id', $order->style_id ?? '') == $style->id)>{{ $style->style_no }} — {{ $style->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Delivery Date</label>
        <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', optional($order->delivery_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Price</label>
        <input type="number" step="0.0001" min="0" name="price" class="form-control" value="{{ old('price', $order->price ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Currency <span class="text-danger">*</span></label>
        <select name="currency" class="form-control">
            @foreach(['USD', 'EUR', 'GBP', 'BDT'] as $cur)
                <option value="{{ $cur }}" @selected(old('currency', $order->currency ?? 'USD') === $cur)>{{ $cur }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control">
            @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'in_production' => 'In Production', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $order->status ?? 'pending') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Total Quantity</label>
        <input type="text" class="form-control" id="orderTotalQty" value="0" disabled>
        <span class="form-text">Auto-calculated from the breakdown below.</span>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $order->description ?? '') }}</textarea>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $order->remarks ?? '') }}</textarea>
    </div>
</div>

<hr>
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">Color / Size Breakdown</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" data-line-items-add="ord"><i class="fa-solid fa-plus"></i> Add Row</button>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle">
        <thead>
            <tr><th style="min-width:200px">Color</th><th style="min-width:160px">Size</th><th style="width:140px">Qty</th><th style="width:40px"></th></tr>
        </thead>
        <tbody id="ordRowsBody">
            @php $lines = old('items', isset($order) ? $order->items->map(fn ($i) => $i->toArray())->all() : [[]]); @endphp
            @foreach($lines as $index => $line)
                <tr>
                    <td>
                        <select name="items[{{ $index }}][color_id]" class="form-control merch-select2">
                            <option value="">— Select —</option>
                            @foreach($colorsOptions as $color)
                                <option value="{{ $color->id }}" @selected(($line['color_id'] ?? null) == $color->id)>{{ $color->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="items[{{ $index }}][size_id]" class="form-control merch-select2">
                            <option value="">— Select —</option>
                            @foreach($sizesOptions as $size)
                                <option value="{{ $size->id }}" @selected(($line['size_id'] ?? null) == $size->id)>{{ $size->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" min="1" name="items[{{ $index }}][qty]" class="form-control" data-role="qty" value="{{ $line['qty'] ?? '' }}" required></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger" data-line-items-remove><i class="fa-solid fa-xmark"></i></button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<template id="ordRowTemplate">
    <tr>
        <td>
            <select name="items[__INDEX__][color_id]" class="form-control merch-select2">
                <option value="">— Select —</option>
                @foreach($colorsOptions as $color)
                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="items[__INDEX__][size_id]" class="form-control merch-select2">
                <option value="">— Select —</option>
                @foreach($sizesOptions as $size)
                    <option value="{{ $size->id }}">{{ $size->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" min="1" name="items[__INDEX__][qty]" class="form-control" data-role="qty" required></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" data-line-items-remove><i class="fa-solid fa-xmark"></i></button></td>
    </tr>
</template>

@push('js')
<script>
    (function () {
        function recomputeTotal() {
            let total = 0;
            document.querySelectorAll('#ordRowsBody [data-role="qty"]').forEach(function (input) {
                total += parseInt(input.value || 0, 10);
            });
            document.getElementById('orderTotalQty').value = total;
        }
        document.addEventListener('input', function (e) {
            if (e.target.matches('#ordRowsBody [data-role="qty"]')) {
                recomputeTotal();
            }
        });
        const observer = new MutationObserver(recomputeTotal);
        observer.observe(document.getElementById('ordRowsBody'), { childList: true });
        recomputeTotal();
    })();
</script>
@endpush
