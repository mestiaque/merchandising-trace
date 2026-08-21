<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('season') ? 'merch_season.edit' : 'merch_season.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_seasons', 'code')->ignore($this->route('season'))->whereNull('deleted_at')],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
