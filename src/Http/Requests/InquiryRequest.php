<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\Inquiry;

class InquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('inquiry') ? 'merch_inquiry.edit' : 'merch_inquiry.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'inquiry_given_date' => ['required', 'date'],
            'buyer_id' => ['required', 'integer', 'exists:mer_buyers,id'],
            'season_id' => ['nullable', 'integer', 'exists:mer_seasons,id'],
            'merchandiser_id' => ['nullable', 'integer', 'exists:users,id'],
            'factory_id' => ['nullable', 'integer', 'exists:mer_factories,id'],
            'order_confirmation_due_date' => ['nullable', 'date'],
            'product_type_id' => ['nullable', 'integer', 'exists:mer_product_types,id'],
            'style_ref' => ['nullable', 'string', 'max:150'],
            'color_ref' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'target_qty' => ['nullable', 'integer', 'min:0'],
            'target_price' => ['nullable', 'numeric', 'min:0'],
            'target_ship_date' => ['nullable', 'date'],
            'extended_ship_date' => ['nullable', 'date', 'after_or_equal:target_ship_date'],
            'status' => ['required', 'string', Rule::in(Inquiry::STATUSES)],
            'lost_reason' => ['nullable', 'string', 'max:255', 'required_if:status,lost'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'target_qty' => 'order qty',
            'target_price' => 'unit price',
            'target_ship_date' => 'ship date',
            'extended_ship_date' => 'extended ship date',
        ];
    }
}
