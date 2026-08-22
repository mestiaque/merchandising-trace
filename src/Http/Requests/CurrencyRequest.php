<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('currency') ? 'merch_currency.edit' : 'merch_currency.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10', Rule::unique('mer_currencies', 'code')->ignore($this->route('currency'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'symbol' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
