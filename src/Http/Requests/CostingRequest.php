<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\Costing;

class CostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('costing') ? 'merch_costing.edit' : 'merch_costing.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'order_id'              => ['required', 'integer', 'exists:mer_orders,id'],
            'type'                  => ['required', Rule::in(array_keys(Costing::TYPES))],
            'fob_price'             => ['required', 'numeric', 'min:0'],
            'fabric_cost'           => ['nullable', 'numeric', 'min:0'],
            'trim_cost'             => ['nullable', 'numeric', 'min:0'],
            'wash_cost'             => ['nullable', 'numeric', 'min:0'],
            'embroidery_print_cost' => ['nullable', 'numeric', 'min:0'],
            'overhead_cost'         => ['nullable', 'numeric', 'min:0'],
            'status'                => ['required', Rule::in(['draft', 'approved'])],
            'remarks'               => ['nullable', 'string'],
        ];
    }
}
