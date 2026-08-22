<div class="mb-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $season->code ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $season->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Year</label>
    <input type="number" name="year" class="form-control" value="{{ old('year', $season->year ?? date('Y')) }}">
</div>
<div class="mb-3">
    <label class="form-label">Start Date</label>
    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($season->start_date ?? null)->format('Y-m-d')) }}">
</div>
<div class="mb-3">
    <label class="form-label">End Date</label>
    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($season->end_date ?? null)->format('Y-m-d')) }}">
</div>
<div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="seasonActive{{ $season->id ?? 'new' }}" @checked(old('is_active', $season->is_active ?? true))>
    <label class="form-check-label" for="seasonActive{{ $season->id ?? 'new' }}">Active</label>
</div>
