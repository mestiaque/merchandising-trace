<div class="table-responsive mb-3">
    <table class="table table-bordered table-sm mb-0">
        <thead>
            <tr><th>Part</th><th>Qty/Garment</th><th>Embellishment</th><th>Placement</th><th>Critical</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($style->parts as $part)
                @php($partName = $trcPartsOptions->firstWhere('id', $part->trc_part_id)?->name ?? "Part #{$part->trc_part_id}")
                <tr>
                    <td>{{ $partName }}</td>
                    <td>{{ $part->qty_per_garment }}</td>
                    <td>
                        @if($part->embellishment_type === 'none')
                            <span class="badge bg-secondary">None</span>
                        @else
                            <span class="badge bg-warning text-dark">{{ ucfirst(str_replace('_', ' ', $part->embellishment_type)) }}</span>
                        @endif
                    </td>
                    <td>{{ $part->placement }}</td>
                    <td>{{ $part->is_critical ? 'Yes' : 'No' }}</td>
                    <td>
                        @can('merch_style.edit')
                            <form method="POST" action="{{ route('merchandising-trace.styles.parts.destroy', [$style, $part]) }}" onsubmit="return confirm('Remove this part?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No parts defined yet. This tab is mandatory before handover to production.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@can('merch_style.edit')
    <form method="POST" action="{{ route('merchandising-trace.styles.parts.store', $style) }}" class="row g-2">
        @csrf
        <div class="col-md-3">
            <select name="trc_part_id" class="form-control merch-select2" required>
                <option value="">— Select Part —</option>
                @foreach($trcPartsOptions as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><input type="number" name="qty_per_garment" class="form-control" value="1" min="1" required></div>
        <div class="col-md-3">
            <select name="embellishment_type" class="form-control" required>
                @foreach(\ME\MerchandisingTrace\Models\StylePart::EMBELLISHMENT_TYPES as $t)
                    <option value="{{ $t }}">{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><input type="text" name="placement" class="form-control" placeholder="Placement"></div>
        <div class="col-md-1 d-flex align-items-center">
            <div class="form-check">
                <input type="checkbox" name="is_critical" value="1" class="form-check-input" id="isCritical">
                <label class="form-check-label" for="isCritical">Critical</label>
            </div>
        </div>
        <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Add</button></div>
    </form>
@endcan

@include('merchandising-trace::admin.partials.select2-init')
