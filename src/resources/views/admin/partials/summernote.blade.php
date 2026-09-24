{{--
    Turns every <textarea> on a merchandising page into a Summernote editor
    (add class "no-rich" to opt a textarea out). Editors inside modals are
    created when the modal opens, so list pages with one edit-modal per row
    stay light. Output of these fields must go through @richtext(...).
--}}
@once
@push('css')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editor.note-frame { background: #fff; margin-bottom: 0; }
    .note-editor.note-frame .note-editable { min-height: 80px; }
    .note-modal { z-index: 1060; }
    .merch-rich-invalid .note-editor.note-frame { border-color: #dc3545; }
</style>
@endpush

@push('js')
<script>
    (function () {
        const toolbar = [
            ['style', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['font', ['fontsize', 'color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'table', 'hr']],
            ['view', ['codeview']],
        ];

        window.merchRichInit = function (scope) {
            if (typeof $ === 'undefined' || !$.fn.summernote) { return; }
            $(scope || document).find('textarea').not('.no-rich, .note-codable').each(function () {
                const $t = $(this);
                if ($t.next('.note-editor').length) { return; }
                // A hidden textarea can't take the browser's "required" check —
                // it is enforced on submit below instead.
                if ($t.prop('required')) {
                    $t.prop('required', false).attr('data-rich-required', '1');
                }
                $t.summernote({
                    height: Math.max(90, (parseInt($t.attr('rows'), 10) || 2) * 45),
                    dialogsInBody: true,
                    placeholder: $t.attr('placeholder') || '',
                    toolbar: toolbar,
                });
            });
        };

        function boot() {
            $('textarea').not('.no-rich').filter(function () { return !$(this).closest('.modal').length; })
                .each(function () { merchRichInit($(this).parent()); });
            $(document).on('shown.bs.modal', '.modal', function () { merchRichInit(this); });
        }

        // Loaded after DOMContentLoaded so it works wherever the host layout
        // puts jQuery relative to the "js" stack.
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof $ === 'undefined') { return; }
            if ($.fn.summernote) { boot(); return; }
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js';
            s.onload = boot;
            document.head.appendChild(s);
        });

        // Capture phase: sync editor HTML into the textarea, blank out an
        // empty editor ("<p><br></p>"), and enforce "required".
        document.addEventListener('submit', function (e) {
            if (typeof $ === 'undefined' || !$.fn.summernote) { return; }
            let invalid = null;
            $(e.target).find('textarea').each(function () {
                const $t = $(this);
                if (!$t.next('.note-editor').length) { return; }
                const empty = $t.summernote('isEmpty');
                const required = $t.attr('data-rich-required') === '1';
                $t.val(empty ? '' : $t.summernote('code'));
                $t.parent().toggleClass('merch-rich-invalid', empty && required);
                if (empty && required && !invalid) { invalid = $t; }
            });
            if (invalid) {
                e.preventDefault();
                e.stopImmediatePropagation();
                invalid.summernote('focus');
            }
        }, true);
    })();
</script>
@endpush
@endonce
