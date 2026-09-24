<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\Bom;

/**
 * bom_type = file   → a buyer PDF is required (unless one is already stored);
 * bom_type = manual → at least one line is required.
 */
class BomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('bom') ? 'merch_bom.edit' : 'merch_bom.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        $hasStoredFile = (bool) $this->route('bom')?->bom_file;
        $manual = $this->input('bom_type') === 'manual';

        return [
            'style_id' => ['required', 'integer', 'exists:mer_styles,id'],
            'bom_type' => ['required', 'string', Rule::in(array_keys(Bom::TYPES))],
            'remarks' => ['nullable', 'string'],
            'bom_file' => [
                Rule::requiredIf(! $manual && ! $hasStoredFile),
                'nullable', 'file', 'mimes:pdf', 'max:20480',
            ],
            'items' => $manual ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'items.*.item_id' => $manual ? ['required', 'integer', 'exists:mer_items,id'] : ['nullable'],
            'items.*.color_id' => ['nullable', 'integer', 'exists:mer_colors,id'],
            'items.*.size_id' => ['nullable', 'integer', 'exists:mer_sizes,id'],
            'items.*.part_name' => ['nullable', 'string', 'max:150'],
            'items.*.consumption' => $manual ? ['required', 'numeric', 'min:0'] : ['nullable'],
            'items.*.uom_id' => ['nullable', 'integer', 'exists:mer_uoms,id'],
            'items.*.wastage_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.supplier_id' => ['nullable', 'integer', 'exists:mer_suppliers,id'],
            'items.*.lead_time_days' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'bom_file.required' => 'Upload the buyer\'s BOM PDF, or switch to "Create BOM".',
            'items.required' => 'Add at least one BOM line, or switch to "Upload Buyer PDF".',
        ];
    }
}
