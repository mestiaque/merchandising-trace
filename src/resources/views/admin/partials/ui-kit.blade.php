{{-- Shared visual design system for every Production module page. Scoped
     under .merch-module so it only affects this page's own markup — include
     once near the top of any index/create/edit view, right after the
     wrapping <div> gets the `merch-module` class added. Targets the existing
     Bootstrap classes already used everywhere (.card, .table, .badge, .btn,
     form.row.g-2 filter bars) so pages get the upgrade without needing their
     HTML restructured. Mirrors sfl-inventory's ui-kit with a teal accent so
     the two modules read as siblings, not clones. --}}
<style>
/* min-width: 0 overrides the flex item default of min-width: auto — without
   it, a flex child never shrinks below its content's natural width, so a
   wide table (e.g. Daily Production Entry's hourly grid) pushes the whole
   page wider instead of scrolling inside its own .table-responsive. */
.merch-module { --merch-accent: #7c3aed; --merch-accent-dark: #6d28d9; --merch-ink: #1f2937; min-width: 0; }

/* Cards */
.merch-module .card { border: none; border-radius: 14px; box-shadow: 0 2px 14px rgba(0,0,0,.07); margin-bottom: 1.25rem; }
.merch-module .card-header {
    background: #fff; border-bottom: 2px solid #e6fbf8; border-radius: 14px 14px 0 0 !important;
    padding: 16px 20px; display: flex; flex-wrap: wrap; gap: 10px;
}
.merch-module .card-header h5, .merch-module .card-header h6 {
    font-weight: 800; color: var(--merch-ink); border-left: 4px solid var(--merch-accent); padding-left: 12px; margin: 0;
}
.merch-module .card-body { padding: 20px; }

/* DataTables' Buttons extension ships its own .dt-buttons wrapper with a
   default margin-bottom, which throws off vertical alignment when the
   Excel button sits inline next to plain Print/All-Reports links in a
   flex card-header row. */
.merch-module .card-header .dt-buttons { margin: 0; display: inline-flex; }

/* Filter bar */
.merch-module form.row.g-2 {
    background: #fbfbfc; border: 1px solid #f0f0f2; border-radius: 10px; margin: 0 0 18px; padding: 14px 12px 4px;
}
.merch-module form.row.g-2 .form-control, .merch-module form.row.g-2 select { border-radius: 8px; }

/* Forms (create/edit) */
.merch-module .form-label { font-weight: 700; font-size: 12.5px; text-transform: uppercase; letter-spacing: .4px; color: #52525b; margin-bottom: 6px; }
.merch-module .form-control, .merch-module select.form-control, .merch-module textarea.form-control {
    border-radius: 8px; border-color: #e4e4e7;
}
.merch-module .form-control:focus, .merch-module select.form-control:focus {
    border-color: var(--merch-accent); box-shadow: 0 0 0 .2rem rgba(13,148,136,.15);
}
.merch-module .form-text { font-size: 12px; color: #9ca3af; }
.merch-module hr { border-top: 2px dashed #f0f0f2; margin: 22px 0; }

/* Tables */
/* overflow-x: auto (not the previous plain "overflow: hidden") — that had
   been silently blocking horizontal scroll on every wide table in the
   module (most visible on the Daily Production Entry hourly grid); overflow-y
   stays hidden only so the rounded corners still clip the header/rows. */
.merch-module .table-responsive { border-radius: 10px; overflow-x: auto; overflow-y: hidden; border: 1px solid #f0f0f2; max-width: 100%; }
.merch-module .table { margin-bottom: 0; }
.merch-module .table thead th {
    background: #ecfdf9; color: #115e59; font-size: 11.5px; text-transform: uppercase; letter-spacing: .4px;
    font-weight: 800; border-bottom: none; padding: 12px 14px; white-space: nowrap;
}
.merch-module .table tbody td { padding: 12px 14px; vertical-align: middle; font-size: 13.5px; color: #374151; border-color: #f4f4f5; }
.merch-module .table-striped tbody tr:nth-of-type(odd) { background-color: #fafafa; }
.merch-module .table tbody tr:hover { background-color: #ecfdf9; }
.merch-module .table tbody tr td.text-center.text-muted { padding: 34px 14px; font-size: 13.5px; }

/* Badges */
.merch-module .badge { border-radius: 999px; padding: 5px 12px; font-size: 11px; font-weight: 700; letter-spacing: .2px; }

/* Buttons */
.merch-module .btn { border-radius: 8px; font-weight: 600; }
.merch-module .btn-sm { border-radius: 7px; padding: .3rem .65rem; font-size: 12.5px; }
.merch-module .btn-primary { background: var(--merch-accent); border-color: var(--merch-accent); }
.merch-module .btn-primary:hover, .merch-module .btn-primary:focus { background: var(--merch-accent-dark); border-color: var(--merch-accent-dark); }
.merch-module .btn-outline-primary { color: var(--merch-accent-dark); border-color: var(--merch-accent); }
.merch-module .btn-outline-primary:hover { background: var(--merch-accent); border-color: var(--merch-accent); }
.merch-module td.text-end .btn, .merch-module td.text-end form { margin-left: 4px; }
.merch-module td.text-end { white-space: nowrap; }

/* Pagination */
.merch-module .pagination { margin-top: 14px; }
.merch-module .page-link { border-radius: 8px; margin: 0 2px; border-color: #f0f0f2; color: var(--merch-ink); }
.merch-module .page-item.active .page-link { background: var(--merch-accent); border-color: var(--merch-accent); }

/* Modals (master CRUD add/edit/delete popups) */
.merch-module .modal-content { border: none; border-radius: 14px; overflow: hidden; }
.merch-module .modal-header { background: #ecfdf9; border-bottom: 1px solid #ccfbf1; padding: 16px 20px; }
.merch-module .modal-header .modal-title { font-weight: 800; color: var(--merch-ink); }
.merch-module .modal-body { padding: 20px; }
.merch-module .modal-footer { border-top: 1px solid #f4f4f5; padding: 14px 20px; }

/* Alerts */
.merch-module .alert { border: none; border-radius: 10px; font-size: 13.5px; }

/* Select2 tweaks to match rounded inputs */
.merch-module .select2-container--default .select2-selection--single { border-radius: 8px !important; border-color: #e4e4e7 !important; height: calc(1.5em + .75rem + 2px) !important; }
</style>
