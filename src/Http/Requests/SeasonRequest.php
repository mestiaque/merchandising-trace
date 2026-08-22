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
            'code' => ['required', 'string', 'max:100', Rule::unique('mer_seasons', 'code')->ignore($this->route('season'))->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
