<div class="row mb-3">
    @forelse($style->images as $image)
        <div class="col-md-3">
            <div class="card">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}" class="card-img-top" style="height:150px;object-fit:cover;">
                <div class="card-body p-2">
                    <div class="small text-muted">{{ ucfirst($image->type) }}</div>
                    <div class="small">{{ $image->caption }}</div>
                    @can('merch_style.edit')
                        <form method="POST" action="{{ route('merchandising-trace.styles.images.destroy', [$style, $image]) }}" onsubmit="return confirm('Remove this image?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-custom danger" title="Remove"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-muted">No images uploaded yet.</div>
    @endforelse
</div>

@can('merch_style.edit')
    <form method="POST" action="{{ route('merchandising-trace.styles.images.store', $style) }}" enctype="multipart/form-data" class="row">
        @csrf
        <div class="col-md-3">
            <select name="type" class="form-control form-control-sm" required>
                @foreach($imageTypesOptions as $t)
                    <option value="{{ $t->code }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><input type="text" name="caption" class="form-control form-control-sm" placeholder="Caption"></div>
        <div class="col-md-3"><input type="file" name="file" class="form-control form-control-sm" accept="image/*" required></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100 btn-sm">Upload</button></div>
    </form>
@endcan
