<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('product_type') ? 'merch_product_type.edit' : 'merch_product_type.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_product_types', 'code')->ignore($this->route('product_type'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:30'],
            'default_smv' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
