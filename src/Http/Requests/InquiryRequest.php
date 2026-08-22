<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'description' => ['nullable', 'string'],
            'target_qty' => ['nullable', 'integer', 'min:0'],
            'target_price' => ['nullable', 'numeric', 'min:0'],
            'target_ship_date' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(['open', 'quoted', 'confirmed', 'lost', 'cancelled'])],
            'lost_reason' => ['nullable', 'string', 'max:255', 'required_if:status,lost'],
            'remarks' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.style_ref' => ['nullable', 'string', 'max:150'],
            'items.*.product_type_id' => ['nullable', 'integer', 'exists:mer_product_types,id'],
            'items.*.color_ref' => ['nullable', 'string', 'max:150'],
            'items.*.qty' => ['nullable', 'integer', 'min:0'],
            'items.*.target_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
        ];
    }
}
