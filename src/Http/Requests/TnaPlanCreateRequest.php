<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\SalesContractPo;

/**
 * The "Add T&A Plan" shortcut form — one screen that gathers everything a
 * T&A plan actually needs (buyer, style, PO) instead of requiring the
 * Sales Contract → PO → Confirm flow first. Under the hood it still
 * creates a real SalesContract + SalesContractPo (T&A stays PO-scoped,
 * per §8), just bundled into a single submit.
 */
class TnaPlanCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('merch_tna.add');
    }

    public function rules(): array
    {
        return [
            // Sales Contract side
            'buyer_id' => ['required', 'integer', 'exists:mer_buyers,id'],
            'season_id' => ['nullable', 'integer', 'exists:mer_seasons,id'],
            'merchandiser_id' => ['nullable', 'integer', 'exists:users,id'],
            'factory_id' => ['nullable', 'integer', 'exists:mer_factories,id'],
            'contract_date' => ['required', 'date'],

            // PO side
            'style_id' => ['required', 'integer', 'exists:mer_styles,id'],
            'color_id' => ['required', 'integer', 'exists:mer_colors,id'],
            'product_type_id' => ['nullable', 'integer', 'exists:mer_product_types,id'],
            'wash_type_id' => ['nullable', 'integer', 'exists:mer_wash_types,id'],
            'po_no' => ['required', 'string', 'max:150', Rule::unique('mer_sales_contract_pos', 'po_no')->whereNull('deleted_at')],
            'po_due_date' => ['nullable', 'date'],
            'po_qty' => ['required', 'integer', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'price_type' => ['nullable', 'string', 'max:10'],
            'ship_mode_id' => ['nullable', 'integer', 'exists:mer_ship_modes,id'],
            'pcd_date' => ['nullable', 'date'],
            'shipment_date' => ['nullable', 'date'],
            'print_emb' => ['nullable', 'string', Rule::in(SalesContractPo::YES_NO_NA)],
            'emb_applique_ih' => ['nullable', 'string', Rule::in(SalesContractPo::YES_NO_NA)],
            'studs_stones_ih' => ['nullable', 'string', Rule::in(SalesContractPo::YES_NO_NA)],
            'heat_seal_ih' => ['nullable', 'string', Rule::in(SalesContractPo::YES_NO_NA)],
            'sizes' => ['required', 'array', 'min:1'],
            'sizes.*.size_id' => ['required', 'integer', 'exists:mer_sizes,id'],
            'sizes.*.qty' => ['required', 'integer', 'min:0'],
        ];
    }
}
