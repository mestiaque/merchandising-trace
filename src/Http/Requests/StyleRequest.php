<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\Style;

class StyleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('style') ? 'merch_style.edit' : 'merch_style.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'style_no' => ['required', 'string', 'max:100', Rule::unique('mer_styles', 'style_no')->ignore($this->route('style'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'buyer_id' => ['required', 'integer', 'exists:mer_buyers,id'],
            'season_id' => ['nullable', 'integer', 'exists:mer_seasons,id'],
            'merchandiser_id' => ['nullable', 'integer', 'exists:users,id'],
            'wash_type_id' => ['nullable', 'integer', 'exists:mer_wash_types,id'],
            'product_type_id' => ['nullable', 'integer', 'exists:mer_product_types,id'],
            'smv' => ['nullable', 'numeric', 'min:0'],
            'cost_smv' => ['nullable', 'numeric', 'min:0'],
            'target_cm' => ['nullable', 'numeric', 'min:0'],
            'fabric_description' => ['nullable', 'string'],
            'development_status' => ['nullable', 'string', Rule::in(Style::DEVELOPMENT_STATUSES)],
            'requires_dev_sample' => ['nullable', 'boolean'],
            'fabric_sourced_by' => ['nullable', 'string', Rule::in(Style::FABRIC_SOURCED_BY)],
            'is_repeat' => ['nullable', 'boolean'],
            'parent_style_id' => ['nullable', 'integer', 'exists:mer_styles,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
