<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StyleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('style') ? 'merch_style.edit' : 'merch_style.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'style_no' => ['required', 'string', 'max:100', Rule::unique('mer_styles', 'style_no')->ignore($this->route('style'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'buyer_id' => ['required', 'integer', 'exists:mer_buyers,id'],
            'brand_id' => ['nullable', 'integer', 'exists:mer_brands,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
