{{-- The host layout already loads jQuery + DataTables + the Buttons/export
     extension globally, so this just wires it onto the current page's
     table. Rows are still server-paginated by Laravel (large tables would
     be too slow to ship to the browser in full), so DataTables' own
     paging/search stay off — it's used purely for sortable headers and the
     Excel export button.

     Only Excel stays here: the PDF button was a client-side jsPDF render
     (looked nothing like the rest of the ERP's documents) and the built-in
     "print" button just window.print()'d the raw table — both replaced by
     a real printMaster2 print view + a "Print" link the page itself puts in
     its card-header (see e.g. admin/buyers/index.blade.php). The Excel
     button is injected into that same card-header via JS rather than left
     in DataTables' own default toolbar row, so no per-page markup change is
     needed for it.

     props (optional): $tableId (default 'merchDataTable') --}}
@php($tableId = $tableId ?? 'merchDataTable')
@push('js')
<script>
    $(function () {
        var $table = $('#{{ $tableId }}');
        var dt = $table.DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: true,
            dom: 't',
        });

        new $.fn.dataTable.Buttons(dt, {
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel"></i> Excel', className: 'btn btn-sm btn-outline-success' },
            ],
        });

        var $slot = $('<span class="d-inline-flex align-items-center"></span>');
        var $cardHeader = $table.closest('.card').find('.card-header').first();
        var $excelPlaceholder = $cardHeader.find('.dt-excel-slot').first();
        if ($excelPlaceholder.length) {
            $excelPlaceholder.replaceWith($slot);
        } else {
            ($cardHeader.length ? $cardHeader : $table.parent()).append($slot);
        }
        dt.buttons().container().appendTo($slot);
    });
</script>
@endpush
