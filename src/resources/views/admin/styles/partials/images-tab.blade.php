<div class="row g-3 mb-3">
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
                            <button type="submit" class="btn btn-sm btn-outline-danger mt-1">Remove</button>
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
    <form method="POST" action="{{ route('merchandising-trace.styles.images.store', $style) }}" enctype="multipart/form-data" class="row g-2">
        @csrf
        <div class="col-md-3">
            <select name="type" class="form-control" required>
                @foreach(\ME\MerchandisingTrace\Http\Controllers\StyleImageController::TYPES as $t)
                    <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4"><input type="text" name="caption" class="form-control" placeholder="Caption"></div>
        <div class="col-md-3"><input type="file" name="file" class="form-control" accept="image/*" required></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Upload</button></div>
    </form>
@endcan
