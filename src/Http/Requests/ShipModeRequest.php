<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('ship_mode') ? 'merch_ship_mode.edit' : 'merch_ship_mode.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_ship_modes', 'code')->ignore($this->route('ship_mode'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
