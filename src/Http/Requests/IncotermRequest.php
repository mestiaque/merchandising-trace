<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncotermRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('incoterm') ? 'merch_incoterm.edit' : 'merch_incoterm.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:10', Rule::unique('mer_incoterms', 'code')->ignore($this->route('incoterm'))->whereNull('deleted_at')],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
