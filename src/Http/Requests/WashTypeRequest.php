<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WashTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('wash_type') ? 'merch_wash_type.edit' : 'merch_wash_type.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_wash_types', 'code')->ignore($this->route('wash_type'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
