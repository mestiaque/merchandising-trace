@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Add T&A Plan') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Add T&amp;A Plan</h4>
            <a href="{{ route('merchandising-trace.tna-plans.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <div class="alert alert-secondary small">
                Everything needed for a T&amp;A plan — buyer, style, PO — is entered right here in one go. Behind the scenes this also creates the Sales Contract + PO line that the plan belongs to.
            </div>
            <form method="POST" action="{{ route('merchandising-trace.tna-plans.store') }}">
                @csrf

                <h6 class="text-muted text-uppercase small mb-3">Buyer / Contract</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Buyer <span class="text-danger">*</span></label>
                        <select name="buyer_id" class="form-control form-control-sm merch-select2" required>
                            <option value="">— Select —</option>
                            @foreach($buyersOptions as $b)
                                <option value="{{ $b->id }}" @selected(old('buyer_id') == $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Season</label>
                        <select name="season_id" class="form-control form-control-sm merch-select2">
                            <option value="">— Select —</option>
                            @foreach($seasonsOptions as $s)
                                <option value="{{ $s->id }}" @selected(old('season_id') == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Merchandiser</label>
                        <select name="merchandiser_id" class="form-control form-control-sm merch-select2">
                            <option value="">— Select —</option>
                            @foreach($merchandisersOptions as $m)
                                <option value="{{ $m->id }}" @selected(old('merchandiser_id') == $m->id)>{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Factory</label>
                        <select name="factory_id" class="form-control form-control-sm merch-select2">
                            <option value="">— Select —</option>
                            @foreach($factoriesOptions as $f)
                                <option value="{{ $f->id }}" @selected(old('factory_id') == $f->id)>{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Contract Date <span class="text-danger">*</span></label>
                        <input type="date" name="contract_date" class="form-control form-control-sm" value="{{ old('contract_date', now()->toDateString()) }}" required>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="text-muted text-uppercase small mb-3">Style / PO</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Style <span class="text-danger">*</span></label>
                        <select name="style_id" class="form-control form-control-sm merch-select2" required>
                            <option value="">— Select —</option>
                            @foreach($stylesOptions as $s)
                                <option value="{{ $s->id }}" @selected(old('style_id') == $s->id)>{{ $s->style_no }} — {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Color <span class="text-danger">*</span></label>
                        <select name="color_id" class="form-control form-control-sm merch-select2" required>
                            <option value="">— Select —</option>
                            @foreach($colorsOptions as $c)
                                <option value="{{ $c->id }}" @selected(old('color_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Product Type</label>
                        <select name="product_type_id" class="form-control form-control-sm merch-select2">
                            <option value="">— Select —</option>
                            @foreach($productTypesOptions as $pt)
                                <option value="{{ $pt->id }}" @selected(old('product_type_id') == $pt->id)>{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Wash Type</label>
                        <select name="wash_type_id" class="form-control form-control-sm merch-select2">
                            <option value="">— Select —</option>
                            @foreach($washTypesOptions as $w)
                                <option value="{{ $w->id }}" @selected(old('wash_type_id') == $w->id)>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">PO No <span class="text-danger">*</span></label>
                        <input type="text" name="po_no" class="form-control form-control-sm" value="{{ old('po_no') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">PO Due Date</label>
                        <input type="date" name="po_due_date" class="form-control form-control-sm" value="{{ old('po_due_date') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">PO Qty <span class="text-danger">*</span></label>
                        <input type="number" min="0" name="po_qty" id="poQtyInput" class="form-control form-control-sm" value="{{ old('po_qty', 0) }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Unit Price</label>
                        <input type="number" step="0.0001" min="0" name="unit_price" class="form-control form-control-sm" value="{{ old('unit_price') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Price Type</label>
                        <select name="price_type" class="form-control form-control-sm">
                            <option value="">— Select —</option>
                            @foreach(['FOB', 'CM', 'CMT', 'CIF', 'DDP', 'FOC'] as $pt)
                                <option value="{{ $pt }}" @selected(old('price_type') === $pt)>{{ $pt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Ship Mode</label>
                        <select name="ship_mode_id" class="form-control form-control-sm merch-select2">
                            <option value="">— Select —</option>
                            @foreach($shipModesOptions as $sm)
                                <option value="{{ $sm->id }}" @selected(old('ship_mode_id') == $sm->id)>{{ $sm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">PCD</label>
                        <input type="date" name="pcd_date" class="form-control form-control-sm" value="{{ old('pcd_date') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Shipment Date</label>
                        <input type="date" name="shipment_date" class="form-control form-control-sm" value="{{ old('shipment_date') }}">
                    </div>
                </div>

                <hr>
                <h6>Embellishment</h6>
                <div class="row">
                    @foreach(['print_emb' => 'Print / Emb', 'emb_applique_ih' => 'EMB Applique IH', 'studs_stones_ih' => 'Studs/Stones IH', 'heat_seal_ih' => 'Heat Seal IH'] as $field => $label)
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ $label }}</label>
                            <select name="{{ $field }}" class="form-control form-control-sm">
                                @foreach(['na' => 'N/A', 'yes' => 'Yes', 'no' => 'No'] as $val => $l)
                                    <option value="{{ $val }}" @selected(old($field, 'na') === $val)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Size Breakdown</h6>
                    <span class="text-muted">Total: <strong id="sizeQtyTotal">0</strong></span>
                </div>
                <div class="row">
                    @foreach($sizesOptions as $size)
                        <div class="col-md-3 mb-2">
                            <label class="form-label">{{ $size->name }}</label>
                            <input type="hidden" name="sizes[{{ $loop->index }}][size_id]" value="{{ $size->id }}">
                            <input type="number" min="0" name="sizes[{{ $loop->index }}][qty]" class="form-control form-control-sm size-qty-input" value="{{ old('sizes.' . $loop->index . '.qty', 0) }}">
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary mt-3 btn-sm">Create T&amp;A Plan</button>
                <a href="{{ route('merchandising-trace.tna-plans.index') }}" class="btn btn-light mt-3 btn-sm">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@push('js')
<script>
    (function () {
        function recomputeSizeTotal() {
            let total = 0;
            document.querySelectorAll('.size-qty-input').forEach(function (el) { total += parseInt(el.value || 0, 10); });
            document.getElementById('sizeQtyTotal').textContent = total;
            document.getElementById('poQtyInput').value = total;
        }
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('size-qty-input')) { recomputeSizeTotal(); }
        });
        recomputeSizeTotal();
    })();
</script>
@endpush
@endsection
