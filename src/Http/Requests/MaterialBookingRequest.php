<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\MaterialBooking;

class MaterialBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('material_booking') ? 'merch_material_booking.edit' : 'merch_material_booking.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'order_id'      => ['required', 'integer', 'exists:mer_orders,id'],
            'supplier_id'   => ['nullable', 'integer', 'exists:mer_suppliers,id'],
            'material_type' => ['required', Rule::in(MaterialBooking::MATERIAL_TYPES)],
            'material_name' => ['required', 'string', 'max:150'],
            'qty'           => ['required', 'numeric', 'min:0.0001'],
            'unit_id'       => ['nullable', 'integer', 'exists:mer_uoms,id'],
            'booking_date'  => ['nullable', 'date'],
            'expected_date' => ['nullable', 'date'],
            'status'        => ['required', Rule::in(['booked', 'received', 'cancelled'])],
            'remarks'       => ['nullable', 'string'],
        ];
    }
}
