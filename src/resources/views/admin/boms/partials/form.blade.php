{{-- props: bom (optional, for edit), stylesOptions --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Style <span class="text-danger">*</span></label>
        <select name="style_id" class="form-control merch-select2" required {{ isset($bom) ? 'disabled' : '' }}>
            <option value="">— Select —</option>
            @foreach($stylesOptions as $style)
                <option value="{{ $style->id }}" @selected(old('style_id', $bom->style_id ?? '') == $style->id)>{{ $style->style_no }} — {{ $style->name }}</option>
            @endforeach
        </select>
        @if(isset($bom))
            <input type="hidden" name="style_id" value="{{ $bom->style_id }}">
            <span class="form-text">A new version keeps the same style — create a fresh BOM to target a different style.</span>
        @endif
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $bom->remarks ?? '') }}</textarea>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">BOM (PDF)</label>
        <input type="file" name="bom_file" class="form-control" accept="application/pdf">
        <span class="form-text">Buyer-provided BOM document — upload the PDF as given, we don't rebuild it line by line.</span>
        @if(!empty($bom?->bom_file))
            <div class="mt-1 small">
                Current file:
                <a href="{{ route('merchandising-trace.boms.file.view', $bom) }}" target="_blank" rel="noopener">View</a>
                &middot;
                <a href="{{ route('merchandising-trace.boms.file.download', $bom) }}">Download</a>
            </div>
        @endif
    </div>
</div>
