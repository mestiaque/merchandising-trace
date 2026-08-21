<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalesContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('sales_contract') ? 'merch_sales_contract.edit' : 'merch_sales_contract.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'order_id'      => ['required', 'integer', 'exists:mer_orders,id'],
            'contract_date' => ['nullable', 'date'],
            'terms'         => ['nullable', 'string'],
            'status'        => ['required', 'string', Rule::in(['draft', 'signed', 'cancelled'])],
            'remarks'       => ['nullable', 'string'],
        ];
    }
}
