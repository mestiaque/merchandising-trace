<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipmentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('shipment_plan') ? 'merch_shipment_plan.edit' : 'merch_shipment_plan.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'order_id'         => ['required', 'integer', 'exists:mer_orders,id'],
            'planned_date'     => ['nullable', 'date'],
            'actual_date'      => ['nullable', 'date'],
            'planned_qty'      => ['required', 'integer', 'min:0'],
            'destination_port' => ['nullable', 'string', 'max:150'],
            'mode'             => ['required', Rule::in(['sea', 'air', 'land'])],
            'status'           => ['required', Rule::in(['planned', 'shipped', 'delivered', 'delayed'])],
            'remarks'          => ['nullable', 'string'],
        ];
    }
}
