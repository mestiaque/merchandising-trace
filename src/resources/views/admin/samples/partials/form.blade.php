{{-- props: sample (optional, for edit), buyersOptions, stylesOptions, sizesOptions --}}
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Buyer <span class="text-danger">*</span></label>
        <select name="buyer_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($buyersOptions as $buyer)
                <option value="{{ $buyer->id }}" @selected(old('buyer_id', $sample->buyer_id ?? '') == $buyer->id)>{{ $buyer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Style <span class="text-danger">*</span></label>
        <select name="style_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($stylesOptions as $style)
                <option value="{{ $style->id }}" @selected(old('style_id', $sample->style_id ?? '') == $style->id)>{{ $style->style_no }} — {{ $style->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Sample Type <span class="text-danger">*</span></label>
        <select name="sample_type" class="form-control" required>
            @foreach(\ME\MerchandisingTrace\Models\Sample::SAMPLE_TYPES as $type)
                <option value="{{ $type }}" @selected(old('sample_type', $sample->sample_type ?? '') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Qty <span class="text-danger">*</span></label>
        <input type="number" min="1" name="qty" class="form-control" value="{{ old('qty', $sample->qty ?? 1) }}" required>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Size</label>
        <select name="size_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($sizesOptions as $size)
                <option value="{{ $size->id }}" @selected(old('size_id', $sample->size_id ?? '') == $size->id)>{{ $size->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control" required>
            @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'sent' => 'Sent to Buyer', 'approved' => 'Approved', 'rejected' => 'Rejected', 'revise' => 'Revise Requested'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $sample->status ?? 'pending') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Request Date</label>
        <input type="date" name="request_date" class="form-control" value="{{ old('request_date', optional($sample->request_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Submission Date</label>
        <input type="date" name="submission_date" class="form-control" value="{{ old('submission_date', optional($sample->submission_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Approval Date</label>
        <input type="date" name="approval_date" class="form-control" value="{{ old('approval_date', optional($sample->approval_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $sample->remarks ?? '') }}</textarea>
    </div>
</div>
