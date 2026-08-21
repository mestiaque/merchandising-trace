<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\BomItem;

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
            'style_id'                 => ['required', 'integer', 'exists:mer_styles,id'],
            'remarks'                  => ['nullable', 'string'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.item_type'        => ['required', 'string', Rule::in(BomItem::ITEM_TYPES)],
            'items.*.material_name'    => ['required', 'string', 'max:150'],
            'items.*.unit_id'          => ['nullable', 'integer', 'exists:mer_uoms,id'],
            'items.*.consumption'      => ['required', 'numeric', 'min:0.0001'],
            'items.*.waste_percent'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.remarks'          => ['nullable', 'string'],
        ];
    }
}
