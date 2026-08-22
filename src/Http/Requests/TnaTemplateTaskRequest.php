<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\TnaTemplateTask;

class TnaTemplateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('merch_tna.edit');
    }

    public function rules(): array
    {
        return [
            'group_name' => ['required', 'string', 'max:100'],
            'task_code' => ['required', 'string', 'max:100'],
            'task_name' => ['required', 'string', 'max:150'],
            'value_type' => ['required', 'string', Rule::in(TnaTemplateTask::VALUE_TYPES)],
            'sequence' => ['nullable', 'integer', 'min:0'],
            'offset_days' => ['required', 'integer'],
            'anchor_field' => ['nullable', 'string', Rule::in(['shipment', 'pcd', 'order_confirm', 'po_due'])],
            'responsible_dept_id' => ['nullable', 'integer', 'exists:mer_departments,id'],
            'is_mandatory' => ['nullable', 'boolean'],
            'blocks_pcd' => ['nullable', 'boolean'],
            'auto_source' => ['nullable', 'string', Rule::in(TnaTemplateTask::AUTO_SOURCES)],
            'auto_source_ref' => ['nullable', 'string', 'max:150'],
        ];
    }
}
