<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('uom') ? 'merch_uom.edit' : 'merch_uom.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('mer_uoms', 'code')->ignore($this->route('uom'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'decimal_places' => ['nullable', 'integer', 'min:0', 'max:4'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
