<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $currency->code ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $currency->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Symbol</label>
    <input type="text" name="symbol" class="form-control" value="{{ old('symbol', $currency->symbol ?? '') }}">
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="currencyActive{{ $currency->id ?? 'new' }}" @checked(old('is_active', $currency->is_active ?? true))>
    <label class="form-check-label" for="currencyActive{{ $currency->id ?? 'new' }}">Active</label>
</div>
