{{-- props: salesContract (optional, for edit), buyersOptions, seasonsOptions, merchandisersOptions, factoriesOptions, inquiriesOptions, currenciesOptions --}}
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Buyer <span class="text-danger">*</span></label>
        <select name="buyer_id" class="form-control merch-select2" required>
            <option value="">— Select —</option>
            @foreach($buyersOptions as $b)
                <option value="{{ $b->id }}" @selected(old('buyer_id', $salesContract->buyer_id ?? '') == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Season</label>
        <select name="season_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($seasonsOptions as $s)
                <option value="{{ $s->id }}" @selected(old('season_id', $salesContract->season_id ?? '') == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Merchandiser</label>
        <select name="merchandiser_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($merchandisersOptions as $m)
                <option value="{{ $m->id }}" @selected(old('merchandiser_id', $salesContract->merchandiser_id ?? '') == $m->id)>{{ $m->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Factory</label>
        <select name="factory_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($factoriesOptions as $f)
                <option value="{{ $f->id }}" @selected(old('factory_id', $salesContract->factory_id ?? '') == $f->id)>{{ $f->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">From Inquiry</label>
        <select name="inquiry_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($inquiriesOptions as $i)
                <option value="{{ $i->id }}" @selected(old('inquiry_id', $salesContract->inquiry_id ?? '') == $i->id)>{{ $i->inquiry_no }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Buyer Order Ref</label>
        <input type="text" name="buyer_order_ref" class="form-control" value="{{ old('buyer_order_ref', $salesContract->buyer_order_ref ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Contract Date <span class="text-danger">*</span></label>
        <input type="date" name="contract_date" class="form-control" value="{{ old('contract_date', optional($salesContract->contract_date ?? null)->format('Y-m-d') ?? now()->toDateString()) }}" required>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Currency</label>
        <select name="currency_id" class="form-control merch-select2">
            <option value="">— Select —</option>
            @foreach($currenciesOptions as $c)
                <option value="{{ $c->id }}" @selected(old('currency_id', $salesContract->currency_id ?? '') == $c->id)>{{ $c->code }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Exchange Rate</label>
        <input type="number" step="0.0001" min="0" name="exchange_rate" class="form-control" value="{{ old('exchange_rate', $salesContract->exchange_rate ?? 1) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Delivery Term</label>
        <select name="delivery_term" class="form-control">
            <option value="">— Select —</option>
            @foreach(['FOB', 'CIF', 'CMT', 'DDP'] as $t)
                <option value="{{ $t }}" @selected(old('delivery_term', $salesContract->delivery_term ?? '') === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Payment Term</label>
        <input type="text" name="payment_term" class="form-control" value="{{ old('payment_term', $salesContract->payment_term ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">LC No</label>
        <input type="text" name="lc_no" class="form-control" value="{{ old('lc_no', $salesContract->lc_no ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">LC Date</label>
        <input type="date" name="lc_date" class="form-control" value="{{ old('lc_date', optional($salesContract->lc_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">LC Value</label>
        <input type="number" step="0.0001" min="0" name="lc_value" class="form-control" value="{{ old('lc_value', $salesContract->lc_value ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">LC Expiry</label>
        <input type="date" name="lc_expiry" class="form-control" value="{{ old('lc_expiry', optional($salesContract->lc_expiry ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $salesContract->remarks ?? '') }}</textarea>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Sales Contract Files</label>
        <input type="file" name="files[]" class="form-control" multiple>
        <span class="form-text">You can select multiple PDFs/documents at once.</span>
        @if(isset($salesContract) && $salesContract->files->isNotEmpty())
            <ul class="list-unstyled mt-2 mb-0 small">
                @foreach($salesContract->files as $file)
                    <li>
                        <i class="fa-solid fa-file"></i> {{ $file->original_name }}
                        &middot;
                        <a href="{{ route('merchandising-trace.sales-contracts.files.view', [$salesContract, $file]) }}" target="_blank" rel="noopener">View</a>
                        &middot;
                        <a href="{{ route('merchandising-trace.sales-contracts.files.download', [$salesContract, $file]) }}">Download</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
