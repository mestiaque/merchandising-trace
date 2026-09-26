<div class="row">
<div class="col-md-3 mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $buyer->name ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control form-control-sm" value="{{ old('code', $buyer->code ?? '') }}" required>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Merchandiser</label>
    <select name="merchandiser_id" class="form-control form-control-sm merch-select2">
        <option value="">— Select —</option>
        @foreach($merchandisersOptions as $m)
            <option value="{{ $m->id }}" @selected(old('merchandiser_id', $buyer->merchandiser_id ?? '') == $m->id)>{{ $m->name }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Region</label>
    <input type="text" name="region" class="form-control form-control-sm" value="{{ old('region', $buyer->region ?? '') }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Agent Name</label>
    <input type="text" name="agent_name" class="form-control form-control-sm" value="{{ old('agent_name', $buyer->agent_name ?? '') }}">
</div>
<div class="col-12 mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" class="form-control form-control-sm" rows="2">{{ old('address', $buyer->address ?? '') }}</textarea>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Contact Person</label>
    <input type="text" name="contact_person" class="form-control form-control-sm" value="{{ old('contact_person', $buyer->contact_person ?? '') }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Phone</label>
    <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $buyer->phone ?? '') }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $buyer->email ?? '') }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Payment Term</label>
    <input type="text" name="payment_term" class="form-control form-control-sm" value="{{ old('payment_term', $buyer->payment_term ?? '') }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Delivery Term</label>
    <select name="delivery_term" class="form-control form-control-sm">
        <option value="">— Select —</option>
        @foreach(['FOB', 'CIF', 'CMT', 'DDP'] as $term)
            <option value="{{ $term }}" @selected(old('delivery_term', $buyer->delivery_term ?? '') === $term)>{{ $term }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Default AQL (%)</label>
    <input type="number" step="0.01" name="default_aql" class="form-control form-control-sm" value="{{ old('default_aql', $buyer->default_aql ?? '') }}">
</div>
<div class="col-md-3 mb-3 d-flex align-items-center"><div class="custom-control custom-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="buyerActive{{ $buyer->id ?? 'new' }}" @checked(old('is_active', $buyer->is_active ?? true))>
    <label class="custom-control-label" for="buyerActive{{ $buyer->id ?? 'new' }}">Active</label>
</div>
</div></div>
