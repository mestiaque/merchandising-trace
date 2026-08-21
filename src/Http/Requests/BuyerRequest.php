<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuyerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('buyer') ? 'merch_buyer.edit' : 'merch_buyer.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_buyers', 'code')->ignore($this->route('buyer'))->whereNull('deleted_at')],
            'merchandiser_id' => ['nullable', 'integer', 'exists:users,id'],
            'address' => ['nullable', 'string'],
            'region' => ['nullable', 'string', 'max:150'],
            'agent_name' => ['nullable', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'payment_term' => ['nullable', 'string', 'max:150'],
            'delivery_term' => ['nullable', 'string', 'max:10'],
            'default_aql' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
