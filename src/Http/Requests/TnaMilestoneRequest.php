<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TnaMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('tna_milestone') ? 'merch_tna.edit' : 'merch_tna.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'order_id'       => ['required', 'integer', 'exists:mer_orders,id'],
            'milestone_name' => ['required', 'string', 'max:150'],
            'planned_date'   => ['required', 'date'],
            'actual_date'    => ['nullable', 'date'],
            'status'         => ['required', Rule::in(['pending', 'completed'])],
            'is_escalated'   => ['nullable', 'boolean'],
            'remarks'        => ['nullable', 'string'],
        ];
    }
}
