<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $port->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $port->code ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Country</label>
    <input type="text" name="country" class="form-control" value="{{ old('country', $port->country ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Type</label>
    <select name="port_type" class="form-control merch-select2">
        @foreach(['sea' => 'Sea', 'air' => 'Air', 'land' => 'Land'] as $val => $label)
            <option value="{{ $val }}" @selected(old('port_type', $port->port_type ?? 'sea') === $val)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="portActive" @checked(old('is_active', $port->is_active ?? true))>
    <label class="form-check-label" for="portActive">Active</label>
</div>
