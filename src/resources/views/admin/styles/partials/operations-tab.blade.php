<div class="table-responsive mb-3">
    <table class="table table-bordered table-sm mb-0">
        <thead><tr><th>#</th><th>Operation</th><th>Machine</th><th>SMV</th><th></th></tr></thead>
        <tbody>
            @forelse($style->operations as $op)
                <tr>
                    <td>{{ $op->sequence }}</td>
                    <td>{{ $op->operation_name }}</td>
                    <td>{{ $op->machine_type }}</td>
                    <td>{{ $op->smv }}</td>
                    <td>
                        @can('merch_style.edit')
                            <form method="POST" action="{{ route('merchandising-trace.styles.operations.destroy', [$style, $op]) }}" onsubmit="return confirm('Remove this operation?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No operations defined yet.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><th colspan="3" class="text-end">Total SMV</th><th>{{ number_format($style->operations->sum('smv'), 4) }}</th><th></th></tr>
        </tfoot>
    </table>
</div>

@can('merch_style.edit')
    <form method="POST" action="{{ route('merchandising-trace.styles.operations.store', $style) }}" class="row g-2">
        @csrf
        <div class="col-md-4"><input type="text" name="operation_name" class="form-control" placeholder="Operation Name" required></div>
        <div class="col-md-3"><input type="text" name="machine_type" class="form-control" placeholder="Machine Type"></div>
        <div class="col-md-2"><input type="number" step="0.0001" name="smv" class="form-control" placeholder="SMV" required></div>
        <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">Add Operation</button></div>
    </form>
@endcan
