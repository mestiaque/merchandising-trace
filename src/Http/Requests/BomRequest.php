<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('bom') ? 'merch_bom.edit' : 'merch_bom.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'style_id' => ['required', 'integer', 'exists:mer_styles,id'],
            'remarks' => ['nullable', 'string'],
            'bom_file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ];
    }
}
