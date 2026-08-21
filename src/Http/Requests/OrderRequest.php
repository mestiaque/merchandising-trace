<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('order') ? 'merch_order.edit' : 'merch_order.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'buyer_id'          => ['required', 'integer', 'exists:mer_buyers,id'],
            'style_id'          => ['required', 'integer', 'exists:mer_styles,id'],
            'description'       => ['nullable', 'string'],
            'delivery_date'     => ['nullable', 'date'],
            'price'             => ['nullable', 'numeric', 'min:0'],
            'currency'          => ['required', 'string', 'max:10'],
            'status'            => ['required', 'string', 'in:pending,confirmed,in_production,completed,cancelled'],
            'remarks'           => ['nullable', 'string'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.color_id'  => ['nullable', 'integer', 'exists:mer_colors,id'],
            'items.*.size_id'   => ['nullable', 'integer', 'exists:mer_sizes,id'],
            'items.*.qty'       => ['required', 'integer', 'min:1'],
        ];
    }
}
