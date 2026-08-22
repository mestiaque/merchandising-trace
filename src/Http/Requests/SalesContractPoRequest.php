<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\SalesContractPo;

class SalesContractPoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('sales_contract_po') ? 'merch_sales_contract.edit' : 'merch_sales_contract.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'style_id' => ['required', 'integer', 'exists:mer_styles,id'],
            'product_type_id' => ['nullable', 'integer', 'exists:mer_product_types,id'],
            'color_id' => ['required', 'integer', 'exists:mer_colors,id'],
            'wash_type_id' => ['nullable', 'integer', 'exists:mer_wash_types,id'],
            'po_no' => ['required', 'string', 'max:150'],
            'po_due_date' => ['nullable', 'date'],
            'po_qty' => ['required', 'integer', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'price_type' => ['nullable', 'string', 'max:10'],
            'cost_smv' => ['nullable', 'numeric', 'min:0'],
            'cm' => ['nullable', 'numeric', 'min:0'],
            'fob_foc' => ['nullable', 'numeric', 'min:0'],
            'pcd_date' => ['nullable', 'date'],
            'shipment_date' => ['nullable', 'date'],
            'ship_mode_id' => ['nullable', 'integer', 'exists:mer_ship_modes,id'],
            'print_emb' => ['nullable', 'string', Rule::in(SalesContractPo::YES_NO_NA)],
            'emb_applique_ih' => ['nullable', 'string', Rule::in(SalesContractPo::YES_NO_NA)],
            'studs_stones_ih' => ['nullable', 'string', Rule::in(SalesContractPo::YES_NO_NA)],
            'heat_seal_ih' => ['nullable', 'string', Rule::in(SalesContractPo::YES_NO_NA)],
            'remarks' => ['nullable', 'string'],
            'sizes' => ['required', 'array', 'min:1'],
            'sizes.*.size_id' => ['required', 'integer', 'exists:mer_sizes,id'],
            'sizes.*.qty' => ['required', 'integer', 'min:0'],
        ];
    }
}
