{{-- props: document (optional, for edit), buyersOptions, ordersOptions --}}
<div class="mb-3">
    <label class="form-label">Document Type <span class="text-danger">*</span></label>
    <select name="document_type" class="form-control" required>
        @foreach(\ME\MerchandisingTrace\Models\Document::DOCUMENT_TYPES as $val => $label)
            <option value="{{ $val }}" @selected(old('document_type', $document->document_type ?? '') === $val)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Title <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $document->title ?? '') }}" required>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Buyer</label>
        <select name="buyer_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($buyersOptions as $buyer)
                <option value="{{ $buyer->id }}" @selected(old('buyer_id', $document->buyer_id ?? '') == $buyer->id)>{{ $buyer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Order</label>
        <select name="order_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($ordersOptions as $order)
                <option value="{{ $order->id }}" @selected(old('order_id', $document->order_id ?? '') == $order->id)>{{ $order->po_number }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">File Path / URL <span class="text-danger">*</span></label>
    <input type="text" name="file_path" class="form-control" value="{{ old('file_path', $document->file_path ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Remarks</label>
    <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $document->remarks ?? '') }}</textarea>
</div>
