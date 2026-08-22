<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\Sample;

class SampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('sample') ? 'merch_sample.edit' : 'merch_sample.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'style_id' => ['required', 'integer', 'exists:mer_styles,id'],
            'buyer_id' => ['required', 'integer', 'exists:mer_buyers,id'],
            'season_id' => ['nullable', 'integer', 'exists:mer_seasons,id'],
            'sample_type_id' => ['required', 'integer', 'exists:mer_sample_types,id'],
            'merchandiser_id' => ['nullable', 'integer', 'exists:users,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'size_ref' => ['nullable', 'string', 'max:150'],
            'color_ref' => ['nullable', 'string', 'max:150'],
            'request_date' => ['nullable', 'date'],
            'required_date' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(Sample::STATUSES)],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
