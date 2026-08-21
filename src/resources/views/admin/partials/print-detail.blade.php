{{--
    Generic printable DOCUMENT view, used by every show-page "Print" button
    (Order, BOM, MRP, Material Issue, Cutting, Printing, Embroidery, Wash,
    Finishing, QC, Packing, Shipment, Sample, Sub Contract Issue/Receive/
    Bill, Line Job). Extends the shared printMaster2 layout, same as
    print-table — see that file's docblock for why (matches sfl-inventory's
    Requisition/Issue "print via printMaster2" convention).

    props:
    - $title       (string)
    - $docNumber   (string|null, shown top-right, e.g. "PO-000001")
    - $meta        (assoc array: label => value, shown as a 2-column grid)
    - $sections    (optional array of ['title' => string, 'columns' => assoc, 'rows' => iterable, 'summaryRow' => optional assoc])
    - $remarks     (optional string)
    - $signatures  (optional array of labels; defaults to Prepared/Checked/Approved By)
--}}
@extends('printMaster2')

@section('title', $title . ($docNumber ? ' - ' . $docNumber : ''))

@section('contents')
    @php
        $signatures = $signatures ?? ['Prepared By', 'Checked By', 'Approved By'];
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

    <div class="print-header">
        <div class="company-info">
            <div>
                <div class="company-name">{{ config('merchandising-trace.company.name') }}</div>
                <div class="company-address">{{ config('merchandising-trace.company.address') }}</div>
            </div>
            <div class="text-right">
                <div class="report-title"><span>{{ $title }}</span></div>
                @if($docNumber)
                    <div style="margin-top:6px;font-weight:bold;">{{ $docNumber }}</div>
                @endif
                <div class="print-time">Printed: {{ now()->format('d M Y, h:i A') }}</div>
            </div>
        </div>
    </div>

    <table style="margin-bottom:15px;">
        <tbody>
            @foreach(array_chunk($meta, 2, true) as $pair)
                <tr>
                    @foreach($pair as $label => $value)
                        <td style="width:160px;"><strong>{{ $label }}</strong></td>
                        <td>{{ $value }}</td>
                    @endforeach
                    @if(count($pair) === 1)
                        <td colspan="2"></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    @foreach(($sections ?? []) as $section)
        <div class="report-title" style="margin-top:15px;"><span>{{ $section['title'] }}</span></div>
        <table>
            <thead>
                <tr>
                    @foreach($section['columns'] as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($section['rows'] as $row)
                    <tr>
                        @foreach($section['columns'] as $key => $label)
                            <td>{{ merchPrintValue($row, $key) }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ count($section['columns']) }}" class="text-center">No lines.</td></tr>
                @endforelse
            </tbody>
            @if(!empty($section['summaryRow']))
                <tfoot>
                    <tr class="grandtotal-row">
                        @foreach($section['columns'] as $key => $label)
                            <th>{{ $section['summaryRow'][$key] ?? '' }}</th>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    @endforeach

    @if(!empty($remarks))
        <p style="margin-top:15px;"><strong>Remarks:</strong> {{ $remarks }}</p>
    @endif

    <div class="print-footer">
        @foreach($signatures as $label)
            <div class="signature-box">
                <div class="signature-line">{{ $label }}</div>
            </div>
        @endforeach
    </div>
@endsection
