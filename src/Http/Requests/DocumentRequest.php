<?php

namespace ME\MerchandisingTrace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\MerchandisingTrace\Models\Document;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->route('document') ? 'merch_document.edit' : 'merch_document.add';

        return (bool) $this->user()?->can($ability);
    }

    public function rules(): array
    {
        return [
            'buyer_id'      => ['nullable', 'integer', 'exists:mer_buyers,id'],
            'order_id'      => ['nullable', 'integer', 'exists:mer_orders,id'],
            'document_type' => ['required', Rule::in(array_keys(Document::DOCUMENT_TYPES))],
            'title'         => ['required', 'string', 'max:150'],
            'file_path'     => ['required', 'string', 'max:255'],
            'remarks'       => ['nullable', 'string'],
        ];
    }
}
