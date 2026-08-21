<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('item_category') ? 'merch_item_category.edit' : 'merch_item_category.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_item_categories', 'code')->ignore($this->route('item_category'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
