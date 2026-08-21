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
            'name' => ['required', 'string', 'max:150'],
            'short_name' => ['required', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
