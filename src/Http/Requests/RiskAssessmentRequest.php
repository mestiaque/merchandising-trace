<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiskAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('risk_assessment') ? 'merch_risk_assessment.edit' : 'merch_risk_assessment.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'style_id' => ['required', 'integer', 'exists:mer_styles,id'],
            'season_id' => ['nullable', 'integer', 'exists:mer_seasons,id'],
            'collection_name' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:150'],
            'sewing_factory_id' => ['nullable', 'integer', 'exists:mer_factories,id'],
            'print_factory_id' => ['nullable', 'integer', 'exists:mer_factories,id'],
            'embroidery_factory_id' => ['nullable', 'integer', 'exists:mer_factories,id'],
            'wash_factory_id' => ['nullable', 'integer', 'exists:mer_factories,id'],
            'design_risk' => ['nullable', 'string'],
            'materials_risk' => ['nullable', 'string'],
            'components_risk' => ['nullable', 'string'],
            'process_risk' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
