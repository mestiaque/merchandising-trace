<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StyleImageTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('style_image_type') ? 'merch_style_image_type.edit' : 'merch_style_image_type.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_style_image_types', 'code')->ignore($this->route('style_image_type'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
