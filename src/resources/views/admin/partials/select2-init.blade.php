@push('js')
<script>
    function prodSelect2Init(scope) {
        if (typeof $ === 'undefined' || !$.fn.select2) { return; }
        $((scope || document).querySelectorAll ? scope || document : document).find('.merch-select2').select2({ width: '100%' });
    }
    document.addEventListener('DOMContentLoaded', function () { prodSelect2Init(document); });
</script>
@endpush
