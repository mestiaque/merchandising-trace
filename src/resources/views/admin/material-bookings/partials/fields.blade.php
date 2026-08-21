{{-- props: material_booking (optional, for edit), ordersOptions, suppliersOptions, unitsOptions --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Order <span class="text-danger">*</span></label>
        <select name="order_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($ordersOptions as $order)
                <option value="{{ $order->id }}" @selected(old('order_id', $material_booking->order_id ?? '') == $order->id)>{{ $order->po_number }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($suppliersOptions as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $material_booking->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Material Type <span class="text-danger">*</span></label>
        <select name="material_type" class="form-control" required>
            @foreach(\ME\MerchandisingTrace\Models\MaterialBooking::MATERIAL_TYPES as $type)
                <option value="{{ $type }}" @selected(old('material_type', $material_booking->material_type ?? '') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Material Name <span class="text-danger">*</span></label>
        <input type="text" name="material_name" class="form-control" value="{{ old('material_name', $material_booking->material_name ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Qty <span class="text-danger">*</span></label>
        <input type="number" step="0.0001" min="0" name="qty" class="form-control" value="{{ old('qty', $material_booking->qty ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Unit</label>
        <select name="unit_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($unitsOptions as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id', $material_booking->unit_id ?? '') == $unit->id)>{{ $unit->short_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control" required>
            <option value="booked" @selected(old('status', $material_booking->status ?? 'booked') === 'booked')>Booked</option>
            <option value="received" @selected(old('status', $material_booking->status ?? 'booked') === 'received')>Received</option>
            <option value="cancelled" @selected(old('status', $material_booking->status ?? 'booked') === 'cancelled')>Cancelled</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Booking Date</label>
        <input type="date" name="booking_date" class="form-control" value="{{ old('booking_date', optional($material_booking->booking_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Expected Date</label>
        <input type="date" name="expected_date" class="form-control" value="{{ old('expected_date', optional($material_booking->expected_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $material_booking->remarks ?? '') }}</textarea>
    </div>
</div>
