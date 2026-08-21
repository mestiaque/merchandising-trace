<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('payment_term') ? 'merch_payment_term.edit' : 'merch_payment_term.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_payment_terms', 'code')->ignore($this->route('payment_term'))->whereNull('deleted_at')],
            'days' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
