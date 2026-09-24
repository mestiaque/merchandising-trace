<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $costSheet->cost_sheet_no }} — Open Cost Sheet</title>
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        body { margin: 0; padding: 16px; background: #fff; }
        .toolbar { margin-bottom: 12px; font-family: Arial, sans-serif; }
        .toolbar button { padding: 6px 14px; cursor: pointer; }
        .cs-page { max-width: 1000px; margin: 0 auto; }
        @media print {
            body { padding: 0; }
            .toolbar { display: none; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="cs-page">
        <div class="toolbar">
            <button type="button" onclick="window.print()">Print</button>
            <button type="button" onclick="window.close()">Close</button>
        </div>
        @include('merchandising-trace::admin.cost-sheets.partials.sheet', ['forPdf' => false])
    </div>
    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
