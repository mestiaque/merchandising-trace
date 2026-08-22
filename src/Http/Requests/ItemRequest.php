<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\Item;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('item') ? 'merch_item.edit' : 'merch_item.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_items', 'code')->ignore($this->route('item'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', 'exists:mer_item_categories,id'],
            'type' => ['required', 'string', Rule::in(Item::TYPES)],
            'uom_id' => ['nullable', 'integer', 'exists:mer_uoms,id'],
            'default_supplier_id' => ['nullable', 'integer', 'exists:mer_suppliers,id'],
            'default_price' => ['nullable', 'numeric', 'min:0'],
            'consumption_uom' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
