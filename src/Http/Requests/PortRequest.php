<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PortRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('port') ? 'merch_port.edit' : 'merch_port.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_ports', 'code')->ignore($this->route('port'))->whereNull('deleted_at')],
            'country' => ['nullable', 'string', 'max:150'],
            'port_type' => ['required', Rule::in(['sea', 'air', 'land'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
