@push('js')
<script>
    function prodSelect2Init(scope) {
        scope = scope || document;
        $(scope).find('.merch-select2').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
                return;
            }
            $(this).select2({
                theme: 'default',
                width: '100%',
                dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : $(document.body),
            });
        });
    }
    $(function () {
        prodSelect2Init(document);
    });
</script>
@endpush
