{{-- SweetAlert2 isn't bundled in the host layout (only jQuery/DataTables/
     Select2 are), so this partial pulls it from CDN itself. Include once per
     page. Provides:
     - a toast for the session('success')/session('error') flash messages
     - a generic SweetAlert2 confirm for any button carrying
       [data-confirm-delete] + data-action="{route}" + optional
       data-confirm-label="{thing}", which submits a same-named hidden
       DELETE form — replaces the old Bootstrap delete-confirm-modal for
       Production Flow pages. --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<form id="prodSwalDeleteForm" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>
@if(session('success'))
    <script>
        Swal.fire({ icon: 'success', title: @json(session('success')), toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
    </script>
@endif
@if(session('error'))
    <script>
        Swal.fire({ icon: 'error', title: @json(session('error')), toast: true, position: 'top-end', showConfirmButton: false, timer: 3500 });
    </script>
@endif
<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-confirm-delete]');
        if (!btn) {
            return;
        }
        e.preventDefault();
        const label = btn.getAttribute('data-confirm-label') || 'this record';
        Swal.fire({
            title: 'Delete ' + label + '?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#dc2626',
        }).then(function (result) {
            if (result.isConfirmed) {
                const form = document.getElementById('prodSwalDeleteForm');
                form.action = btn.getAttribute('data-action');
                form.submit();
            }
        });
    });
</script>
