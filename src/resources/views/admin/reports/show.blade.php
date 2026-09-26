@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle($title) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.ui-kit')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $title }}</h4>
            <div>
                <a href="{{ route('merchandising-trace.reports.export.excel', array_merge(['key' => $key], $filters)) }}" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-excel"></i> Excel</a>
                <a href="{{ route('merchandising-trace.reports.export.pdf', array_merge(['key' => $key], $filters)) }}" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                <a href="{{ route('merchandising-trace.reports.print', array_merge(['key' => $key], $filters)) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-print"></i> Print</a>
                <a href="{{ route('merchandising-trace.reports.index') }}" class="btn btn-sm btn-light"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-3 mb-2">
                    <select name="buyer_id" class="form-control form-control-sm">
                        <option value="">— Buyer —</option>
                        @foreach($buyersOptions as $b)
                            <option value="{{ $b->id }}" @selected(($filters['buyer_id'] ?? null) == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2"><input type="text" name="po_no" class="form-control form-control-sm" placeholder="PO No" value="{{ $filters['po_no'] ?? '' }}"></div>
                <div class="col-md-3 mb-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}"></div>
                <div class="col-md-3 mb-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}"></div>
                <div class="col-md-3 mb-2 d-flex align-items-end flex-wrap gap-1">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                    <a href="{{ url()->current() }}" class="btn btn-light btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            @foreach($headers as $h)
                                <th>{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                @foreach($row as $val)
                                    <td>{{ $val }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ max(1, count($headers)) }}" class="text-center text-muted">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
