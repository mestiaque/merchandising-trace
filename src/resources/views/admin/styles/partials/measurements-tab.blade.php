<div class="d-flex gap-2 mb-3">
    <a href="{{ route('merchandising-trace.styles.measurements.export', $style) }}" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-excel"></i> Export</a>
    @can('merch_style.edit')
        <form method="POST" action="{{ route('merchandising-trace.styles.measurements.import', $style) }}" enctype="multipart/form-data" class="d-flex gap-1">
            @csrf
            <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
            <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">Import</button>
        </form>
    @endcan
</div>

<div class="table-responsive mb-3">
    <table class="table table-bordered table-sm mb-0">
        <thead>
            <tr>
                <th>POM Code</th>
                <th>POM Name</th>
                <th>Tol +</th>
                <th>Tol -</th>
                @foreach($sizesOptions as $size)
                    <th>{{ $size->name }}</th>
                @endforeach
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($style->measurements as $m)
                <tr>
                    <td>{{ $m->pom_code }}</td>
                    <td>{{ $m->pom_name }}</td>
                    <td>{{ $m->tolerance_plus }}</td>
                    <td>{{ $m->tolerance_minus }}</td>
                    @foreach($sizesOptions as $size)
                        <td>{{ $m->sizes->firstWhere('size_id', $size->id)?->value ?? '-' }}</td>
                    @endforeach
                    <td>
                        @can('merch_style.edit')
                            <form method="POST" action="{{ route('merchandising-trace.styles.measurements.destroy', [$style, $m]) }}" onsubmit="return confirm('Remove this row?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ 5 + count($sizesOptions) }}" class="text-center text-muted">No measurement rows yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@can('merch_style.edit')
    <form method="POST" action="{{ route('merchandising-trace.styles.measurements.store', $style) }}" class="row g-2">
        @csrf
        <div class="col-md-2"><input type="text" name="pom_code" class="form-control" placeholder="POM Code" required></div>
        <div class="col-md-3"><input type="text" name="pom_name" class="form-control" placeholder="POM Name" required></div>
        <div class="col-md-1"><input type="number" step="0.01" name="tolerance_plus" class="form-control" placeholder="Tol+"></div>
        <div class="col-md-1"><input type="number" step="0.01" name="tolerance_minus" class="form-control" placeholder="Tol-"></div>
        @foreach($sizesOptions as $size)
            <div class="col-md-1"><input type="number" step="0.01" name="values[{{ $size->id }}]" class="form-control" placeholder="{{ $size->name }}"></div>
        @endforeach
        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Add Row</button></div>
    </form>
@endcan
