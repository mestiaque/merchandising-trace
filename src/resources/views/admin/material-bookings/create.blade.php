@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Add Material Booking') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add Material Booking</h5>
            <a href="{{ route('merchandising-trace.material-bookings.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.material-bookings.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            @foreach(\ME\MerchandisingTrace\Models\MaterialBooking::TYPES as $t)
                                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Sales Contract <span class="text-danger">*</span></label>
                        <select name="sales_contract_id" class="form-control merch-select2" required>
                            <option value="">— Select —</option>
                            @foreach($salesContractsOptions as $sc)
                                <option value="{{ $sc->id }}">{{ $sc->contract_no }} — {{ $sc->buyer->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Style <span class="text-danger">*</span></label>
                        <select name="style_id" class="form-control merch-select2" required>
                            <option value="">— Select —</option>
                            @foreach($stylesOptions as $s)
                                <option value="{{ $s->id }}">{{ $s->style_no }} — {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-control merch-select2">
                            <option value="">— Select —</option>
                            @foreach($suppliersOptions as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mill / Country</label>
                        <input type="text" name="mill_country" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Currency</label>
                        <select name="currency_id" class="form-control merch-select2">
                            <option value="">— Select —</option>
                            @foreach($currenciesOptions as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr>
                <h6>Items to Book</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>Item</th><th>Color</th><th>Booked Qty</th><th>UOM</th><th>Rate</th></tr></thead>
                        <tbody id="bookingItemsBody">
                            @for($i = 0; $i < 5; $i++)
                                <tr>
                                    <td>
                                        <select name="items[{{ $i }}][item_id]" class="form-control merch-select2">
                                            <option value="">— Select —</option>
                                            @foreach($itemsOptions as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[{{ $i }}][color_id]" class="form-control merch-select2">
                                            <option value="">—</option>
                                            @foreach($colorsOptions as $color)
                                                <option value="{{ $color->id }}">{{ $color->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.0001" min="0" name="items[{{ $i }}][booked_qty]" class="form-control"></td>
                                    <td>
                                        <select name="items[{{ $i }}][uom_id]" class="form-control merch-select2">
                                            <option value="">—</option>
                                            @foreach($uomsOptions as $uom)
                                                <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.0001" min="0" name="items[{{ $i }}][rate]" class="form-control"></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary">Save Booking</button>
                <a href="{{ route('merchandising-trace.material-bookings.index') }}" class="btn btn-light">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
