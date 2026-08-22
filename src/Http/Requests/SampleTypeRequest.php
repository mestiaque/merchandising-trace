<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SampleTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('sample_type') ? 'merch_sample_type.edit' : 'merch_sample_type.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_sample_types', 'code')->ignore($this->route('sample_type'))],
            'name' => ['required', 'string', 'max:150'],
            'sequence' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
