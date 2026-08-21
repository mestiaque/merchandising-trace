@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('BOM ' . ($bom->style->style_no ?? '')) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.ui-kit')
    @include('merchandising-trace::admin.partials.sweetalert-init')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">BOM — {{ $bom->style->style_no ?? '' }} — {{ $bom->style->name ?? '' }} (v{{ $bom->version }})</h5>
            <div>
                <a href="{{ route('merchandising-trace.boms.print', $bom) }}" target="_blank" class="btn btn-outline-secondary btn-sm me-1"><i class="fa-solid fa-print"></i> Print</a>
                @can('merch_bom.add')
                    <form method="POST" action="{{ route('merchandising-trace.boms.new-version', $bom) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm me-1"><i class="fa-solid fa-code-branch"></i> New Version</button>
                    </form>
                @endcan
                <a href="{{ route('merchandising-trace.boms.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Buyer:</strong> {{ $bom->style->buyer->name ?? '-' }}</div>
                <div class="col-md-4"><strong>Version:</strong> {{ $bom->version }}</div>
                <div class="col-md-4"><strong>Status:</strong> {{ ucfirst($bom->status) }}</div>
                @if($bom->remarks)
                    <div class="col-12 mt-2"><strong>Remarks:</strong> {{ $bom->remarks }}</div>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr><th>Item Type</th><th>Material</th><th>Consumption</th><th>Waste %</th><th>Unit</th><th>Remarks</th></tr>
                    </thead>
                    <tbody>
                        @foreach($bom->items as $item)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $item->item_type)) }}</td>
                                <td>{{ $item->material_name }}</td>
                                <td>{{ $item->consumption }}</td>
                                <td>{{ $item->waste_percent }}%</td>
                                <td>{{ $item->unit->short_name ?? '-' }}</td>
                                <td>{{ $item->remarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
