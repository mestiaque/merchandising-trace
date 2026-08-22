<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\CostSheetItem;

class CostSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('cost_sheet') ? 'merch_costing.edit' : 'merch_costing.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'style_id' => ['required', 'integer', 'exists:mer_styles,id'],
            'buyer_id' => ['required', 'integer', 'exists:mer_buyers,id'],
            'currency_id' => ['nullable', 'integer', 'exists:mer_currencies,id'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'order_qty' => ['nullable', 'integer', 'min:0'],
            'smv' => ['nullable', 'numeric', 'min:0'],
            'cm_minute_rate' => ['nullable', 'numeric', 'min:0'],
            'efficiency_percent' => ['nullable', 'numeric', 'min:1', 'max:200'],
            'print_emb_cost' => ['nullable', 'numeric', 'min:0'],
            'wash_cost' => ['nullable', 'numeric', 'min:0'],
            'freight_cost' => ['nullable', 'numeric', 'min:0'],
            'testing_cost' => ['nullable', 'numeric', 'min:0'],
            'overhead_cost' => ['nullable', 'numeric', 'min:0'],
            'profit_percent' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'buyer_target_price' => ['nullable', 'numeric', 'min:0'],
            'final_price' => ['nullable', 'numeric', 'min:0'],
            'price_type' => ['required', 'string', Rule::in(CostSheet::PRICE_TYPES)],
            'remarks' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.group' => ['required_with:items', 'string', Rule::in(CostSheetItem::GROUPS)],
            'items.*.item_id' => ['nullable', 'integer', 'exists:mer_items,id'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.consumption' => ['nullable', 'numeric', 'min:0'],
            'items.*.uom_id' => ['nullable', 'integer', 'exists:mer_uoms,id'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
