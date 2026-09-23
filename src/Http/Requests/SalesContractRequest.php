<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'buyer_id' => ['required', 'integer', 'exists:mer_buyers,id'],
            'season_id' => ['nullable', 'integer', 'exists:mer_seasons,id'],
            'merchandiser_id' => ['nullable', 'integer', 'exists:users,id'],
            'factory_id' => ['nullable', 'integer', 'exists:mer_factories,id'],
            'inquiry_id' => ['nullable', 'integer', 'exists:mer_inquiries,id'],
            'buyer_order_ref' => ['nullable', 'string', 'max:150'],
            'contract_date' => ['required', 'date'],
            'currency_id' => ['nullable', 'integer', 'exists:mer_currencies,id'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'delivery_term' => ['nullable', 'string', 'max:10'],
            'payment_term' => ['nullable', 'string', 'max:150'],
            'lc_no' => ['nullable', 'string', 'max:150'],
            'lc_date' => ['nullable', 'date'],
            'lc_value' => ['nullable', 'numeric', 'min:0'],
            'lc_expiry' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:20480'],
        ];
    }
}
