<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('bom') ? 'merch_bom.edit' : 'merch_bom.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'style_id' => ['required', 'integer', 'exists:mer_styles,id'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'exists:mer_items,id'],
            'items.*.color_id' => ['nullable', 'integer', 'exists:mer_colors,id'],
            'items.*.size_id' => ['nullable', 'integer', 'exists:mer_sizes,id'],
            'items.*.part_name' => ['nullable', 'string', 'max:150'],
            'items.*.consumption' => ['required', 'numeric', 'min:0'],
            'items.*.uom_id' => ['nullable', 'integer', 'exists:mer_uoms,id'],
            'items.*.wastage_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.supplier_id' => ['nullable', 'integer', 'exists:mer_suppliers,id'],
            'items.*.lead_time_days' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
