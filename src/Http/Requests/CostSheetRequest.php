<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\CostSheetItem;

/**
 * A sheet is costed against a style, an inquiry, or just a free-text style
 * ref — at least one of style_id / style_ref identifies it.
 */
class CostSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('cost_sheet') ? 'merch_costing.edit' : 'merch_costing.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        $image = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        return [
            'style_id' => ['nullable', 'integer', 'exists:mer_styles,id'],
            'inquiry_id' => ['nullable', 'integer', 'exists:mer_inquiries,id'],
            'style_ref' => ['nullable', 'required_without:style_id', 'string', 'max:150'],
            'buyer_id' => ['required', 'integer', 'exists:mer_buyers,id'],
            'garment_description' => ['nullable', 'string', 'max:255'],
            'size_range' => ['nullable', 'string', 'max:100'],
            'costing_date' => ['nullable', 'date'],
            'currency_id' => ['nullable', 'integer', 'exists:mer_currencies,id'],
            'order_qty' => ['nullable', 'integer', 'min:0'],
            'smv' => ['nullable', 'numeric', 'min:0'],
            'cm_minute_rate' => ['nullable', 'numeric', 'min:0'],
            'efficiency_percent' => ['nullable', 'numeric', 'min:1', 'max:200'],
            'cm_per_dozen' => ['nullable', 'numeric', 'min:0'],
            'commercial_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'buyer_target_price' => ['nullable', 'numeric', 'min:0'],
            'final_price' => ['nullable', 'numeric', 'min:0'],
            'price_type' => ['required', 'string', Rule::in(CostSheet::PRICE_TYPES)],
            'remarks' => ['nullable', 'string'],
            'front_image' => $image,
            'back_image' => $image,
            'sketch_image' => $image,
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['string', Rule::in(['front_image', 'back_image', 'sketch_image'])],
            'items' => ['nullable', 'array'],
            'items.*.group' => ['required', 'string', Rule::in(array_keys(CostSheetItem::GROUPS))],
            'items.*.item_id' => ['nullable', 'integer', 'exists:mer_items,id'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.supplier_name' => ['nullable', 'string', 'max:150'],
            'items.*.consumption' => ['nullable', 'numeric', 'min:0'],
            'items.*.uom_id' => ['nullable', 'integer', 'exists:mer_uoms,id'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'style_ref' => 'style',
            'cm_per_dozen' => 'CM / dozen',
            'items.*.consumption' => 'consumption',
            'items.*.rate' => 'unit price',
        ];
    }

    public function messages(): array
    {
        return ['style_ref.required_without' => 'Pick a style, or type the style reference.'];
    }
}
