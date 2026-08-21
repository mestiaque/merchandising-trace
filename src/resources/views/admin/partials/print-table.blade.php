{{--
    Generic printable LIST view, used by every Masters/Reports/index "Print"
    button across the Production module. Extends the host app's shared
    printMaster2 layout (Print/Close bar + @media print rules) so every
    print-out in this package looks and behaves the same as the rest of the
    ERP (sfl-inventory, hr, acc-sfl) — no PDF generation involved, this is a
    plain browser-print page.

    props:
    - $title       (string)
    - $columns     (assoc array: column key => header label)
    - $rows        (iterable of arrays/objects; dot-notation keys allowed, e.g. 'buyer.name')
    - $summaryRow  (optional assoc array: column key => footer value)
    - $meta        (optional assoc array: label => value, shown above the table, e.g. filters applied)
--}}
@extends('printMaster2')

@section('title', $title)

@section('contents')
    <div class="print-header">
        <div class="company-info">
            <div>
                <div class="company-name">{{ config('merchandising-trace.company.name') }}</div>
                <div class="company-address">{{ config('merchandising-trace.company.address') }}</div>
            </div>
            <div class="text-right">
                <div class="report-title"><span>{{ $title }}</span></div>
                <div class="print-time">Printed: {{ now()->format('d M Y, h:i A') }}</div>
            </div>
        </div>
    </div>

    @if(!empty($meta))
        <table style="margin-bottom:10px;">
            <tbody>
                @foreach($meta as $label => $value)
                    <tr><td style="width:180px;"><strong>{{ $label }}</strong></td><td>{{ $value }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                @foreach($columns as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                if (! function_exists('merchPrintValue')) {
                    function merchPrintValue($row, string $key) {
                        $value = $row;
                        foreach (explode('.', $key) as $segment) {
                            $value = is_array($value) ? ($value[$segment] ?? null) : ($value->{$segment} ?? null);
                        }
                        return $value;
                    }
                }
            @endphp
            @forelse($rows as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    @foreach($columns as $key => $label)
                        <td>{{ merchPrintValue($row, $key) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($columns) + 1 }}" class="text-center">No records.</td></tr>
            @endforelse
        </tbody>
        @if(!empty($summaryRow))
            <tfoot>
                <tr class="grandtotal-row">
                    <th>Total</th>
                    @foreach($columns as $key => $label)
                        <th>{{ $summaryRow[$key] ?? '' }}</th>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
