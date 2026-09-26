@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Add Sub-T&A Plan') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Add Sub-T&amp;A Plan (Embroidery / Print / After-Wash)</h4>
            <a href="{{ route('merchandising-trace.tna-sub-plans.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.tna-sub-plans.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">PO <span class="text-danger">*</span></label>
                        <select name="sales_contract_po_id" class="form-control form-control-sm merch-select2" required>
                            <option value="">— Select —</option>
                            @foreach($posOptions as $po)
                                <option value="{{ $po->id }}">{{ $po->po_no }} — {{ $po->style->style_no ?? '' }} ({{ $po->salesContract->buyer->name ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Process Type <span class="text-danger">*</span></label>
                        <select name="process_type" class="form-control form-control-sm" required>
                            @foreach(\ME\MerchandisingTrace\Models\TnaSubPlan::PROCESS_TYPES as $pt)
                                <option value="{{ $pt }}">{{ ucfirst(str_replace('_', ' ', $pt)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">EMB/Print Type</label>
                        <input type="text" name="emb_print_type" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Required PSD</label>
                        <input type="date" name="required_psd" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Required PFD</label>
                        <input type="date" name="required_pfd" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Required Qty/Day</label>
                        <input type="number" min="0" name="required_qty_per_day" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Plant Name</label>
                        <input type="text" name="plant_name" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Vendor</label>
                        <select name="vendor_id" class="form-control form-control-sm merch-select2">
                            <option value="">— Select —</option>
                            @foreach($vendorsOptions as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">PO Qty <span class="text-danger">*</span></label>
                        <input type="number" min="0" name="po_qty" class="form-control form-control-sm" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <a href="{{ route('merchandising-trace.tna-sub-plans.index') }}" class="btn btn-light btn-sm">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
