<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class OrderDocument extends Model
{
    use HasAudit;
    protected $table = 'mer_order_documents';

    public const STATUSES = ['pending', 'uploaded', 'approved', 'rejected'];

    protected $fillable = [
        'sales_contract_id', 'document_template_item_id', 'name', 'is_mandatory', 'due_date',
        'status', 'file_path', 'uploaded_at', 'uploaded_by', 'remarks',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'due_date' => 'date',
        'uploaded_at' => 'datetime',
    ];

    public function salesContract(): BelongsTo
    {
        return $this->belongsTo(SalesContract::class, 'sales_contract_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && ! in_array($this->status, ['uploaded', 'approved'], true);
    }
}
