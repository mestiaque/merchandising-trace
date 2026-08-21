<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FactoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('factory') ? 'merch_factory.edit' : 'merch_factory.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_factories', 'code')->ignore($this->route('factory'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'unit_type' => ['nullable', 'string', 'max:50'],
            'capacity_per_month' => ['nullable', 'integer', 'min:0'],
            'is_own' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
