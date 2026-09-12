<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class DocumentTemplateItem extends Model
{
    use HasAudit;
    protected $table = 'mer_document_template_items';

    protected $fillable = ['document_template_id', 'name', 'due_offset_days', 'is_mandatory', 'sequence'];

    protected $casts = ['is_mandatory' => 'boolean', 'due_offset_days' => 'integer'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }
}
