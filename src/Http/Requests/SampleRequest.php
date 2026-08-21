<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
            'buyer_id'         => ['required', 'integer', 'exists:mer_buyers,id'],
            'style_id'         => ['required', 'integer', 'exists:mer_styles,id'],
            'order_id'         => ['nullable', 'integer'],
            'sample_type'      => ['required', 'string', 'in:' . implode(',', Sample::SAMPLE_TYPES)],
            'qty'              => ['required', 'integer', 'min:1'],
            'size_id'          => ['nullable', 'integer', 'exists:mer_sizes,id'],
            'request_date'     => ['nullable', 'date'],
            'submission_date'  => ['nullable', 'date'],
            'approval_date'    => ['nullable', 'date'],
            'status'           => ['required', 'string', 'in:pending,in_progress,sent,approved,rejected,revise'],
            'remarks'          => ['nullable', 'string'],
        ];
    }
}
