{{--
    Generic report renderer shared by every Reports > * page.
    props:
    - $title        (string)
    - $reportRoute  (string, e.g. 'production.reports.daily-production' — used for the filter form action)
    - $columns      (assoc array: column key => header label)
    - $rows         (iterable of arrays/objects; each must expose every $columns key, dot-notation allowed e.g. 'buyer.name')
    - $filters      (array of filter field defs: ['name','label','type' => text|date|select, 'options' (Collection with ->id/->name) for select])
    - $summaryRow   (optional assoc array: column key => footer value, for a totals row)
--}}
@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle($title) }}</title>
@endsection

@php
    if (! function_exists('merchReportValue')) {
        function merchReportValue($row, string $key) {
            $value = $row;
            foreach (explode('.', $key) as $segment) {
                $value = is_array($value) ? ($value[$segment] ?? null) : ($value->{$segment} ?? null);
            }
            return $value;
        }
    }
@endphp

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $title }}</h5>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print</a>
                <span class="dt-excel-slot"></span>
                <a href="{{ route('merchandising-trace.reports.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> All Reports</a>
            </div>
        </div>
        <div class="card-body">
            @if(!empty($filters))
                <form method="GET" class="row g-2 mb-3">
                    @foreach($filters as $filter)
                        <div class="col-md-2">
                            @if($filter['type'] === 'select')
                                <select name="{{ $filter['name'] }}" class="form-control merch-select2">
                                    <option value="">{{ $filter['label'] }}</option>
                                    @foreach($filter['options'] as $opt)
                                        <option value="{{ $opt->id }}" @selected(request($filter['name']) == $opt->id)>{{ $opt->{$filter['option_label'] ?? 'name'} }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $filter['type'] }}" name="{{ $filter['name'] }}" class="form-control" placeholder="{{ $filter['label'] }}" value="{{ request($filter['name']) }}">
                            @endif
                        </div>
                    @endforeach
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ url()->current() }}" class="btn btn-light w-100">Reset</a>
                    </div>
                </form>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="merchDataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            @foreach($columns as $label)
                                <th>{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                @foreach($columns as $key => $label)
                                    <td>{{ merchReportValue($row, $key) }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 1 }}" class="text-center text-muted">No data for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(!empty($summaryRow))
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                @foreach($columns as $key => $label)
                                    <th>{{ $summaryRow[$key] ?? '' }}</th>
                                @endforeach
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@include('merchandising-trace::admin.partials.datatable-init')
@endsection
