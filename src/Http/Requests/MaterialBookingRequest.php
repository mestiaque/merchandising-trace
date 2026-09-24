<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\MaterialBooking;

class MaterialBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can($this->route('material_booking') ? 'merch_material_booking.edit' : 'merch_material_booking.add');
    }

    public function messages(): array
    {
        return ['sales_contract_id.exists' => 'The selected LC was not found — pick a contract that has an LC number.'];
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(MaterialBooking::TYPES)],
            'booking_against' => ['required', 'string', Rule::in(array_keys(MaterialBooking::BOOKING_AGAINST))],
            // Booking against an LC: the contract picked must actually carry one.
            'sales_contract_id' => $this->input('booking_against') === 'lc'
                ? ['required', 'integer', Rule::exists('mer_sales_contracts', 'id')->whereNotNull('lc_no')->whereNot('lc_no', '')]
                : ['required', 'integer', 'exists:mer_sales_contracts,id'],
            'sales_contract_po_id' => ['nullable', 'integer', 'exists:mer_sales_contract_pos,id'],
            'style_id' => ['required', 'integer', 'exists:mer_styles,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:mer_suppliers,id'],
            'mill_country' => ['nullable', 'string', 'max:150'],
            'booking_date' => ['nullable', 'date'],
            'pi_no' => ['nullable', 'string', 'max:150'],
            'pi_date' => ['nullable', 'date'],
            'pi_value' => ['nullable', 'numeric', 'min:0'],
            'currency_id' => ['nullable', 'integer', 'exists:mer_currencies,id'],
            'lc_no' => ['nullable', 'string', 'max:150'],
            'lc_date' => ['nullable', 'date'],
            'lc_value' => ['nullable', 'numeric', 'min:0'],
            'lc_type' => ['nullable', 'string', Rule::in(MaterialBooking::LC_TYPES)],
            'x_mill_date' => ['nullable', 'date'],
            'expected_inhouse_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.item_id' => ['required_with:items', 'integer', 'exists:mer_items,id'],
            'items.*.color_id' => ['nullable', 'integer', 'exists:mer_colors,id'],
            'items.*.booked_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.uom_id' => ['nullable', 'integer', 'exists:mer_uoms,id'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
