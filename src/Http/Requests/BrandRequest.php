<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('brand') ? 'merch_brand.edit' : 'merch_brand.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_brands', 'code')->ignore($this->route('brand'))->whereNull('deleted_at')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
