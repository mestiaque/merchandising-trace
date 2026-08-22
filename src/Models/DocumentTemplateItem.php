<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTemplateItem extends Model
{
    protected $table = 'mer_document_template_items';

    protected $fillable = ['document_template_id', 'name', 'due_offset_days', 'is_mandatory', 'sequence'];

    protected $casts = ['is_mandatory' => 'boolean'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }
}
