<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_suppliers', 'code')->ignore($this->route('supplier'))->whereNull('deleted_at')],
            'address' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
