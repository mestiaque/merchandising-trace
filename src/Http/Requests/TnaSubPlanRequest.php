<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\TnaSubPlan;

class TnaSubPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('merch_tna.edit');
    }

    public function rules(): array
    {
        return [
            'sales_contract_po_id' => ['required', 'integer', 'exists:mer_sales_contract_pos,id'],
            'process_type' => ['required', 'string', Rule::in(TnaSubPlan::PROCESS_TYPES)],
            'emb_print_type' => ['nullable', 'string', 'max:150'],
            'required_psd' => ['nullable', 'date'],
            'required_pfd' => ['nullable', 'date', 'after_or_equal:required_psd'],
            'required_qty_per_day' => ['nullable', 'integer', 'min:0'],
            'plant_name' => ['nullable', 'string', 'max:150'],
            'vendor_id' => ['nullable', 'integer', 'exists:mer_suppliers,id'],
            'po_qty' => ['required', 'integer', 'min:0'],
        ];
    }
}
