<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\Supplier;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('supplier') ? 'merch_supplier.edit' : 'merch_supplier.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_suppliers', 'code')->ignore($this->route('supplier'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'string', Rule::in(Supplier::TYPES)],
            'country' => ['nullable', 'string', 'max:100'],
            'contact' => ['nullable', 'string', 'max:150'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'payment_term' => ['nullable', 'string', 'max:150'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
