{{-- props: modalId, label --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="{{ $modalId }}Form">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Delete {{ ucfirst($label) }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this {{ rtrim($label, 's') }}? This action cannot be undone.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-target="#{{ $modalId }}"]');
        if (btn) {
            document.getElementById('{{ $modalId }}Form').action = btn.getAttribute('data-action');
        }
    });
</script>
@endpush
