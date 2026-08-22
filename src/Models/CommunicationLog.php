<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationLog extends Model
{
    protected $table = 'mer_communication_logs';

    public const DIRECTIONS = ['inbound', 'outbound'];
    public const CHANNELS = ['email', 'whatsapp', 'call', 'meeting', 'other'];

    protected $fillable = [
        'style_id', 'sales_contract_po_id', 'log_date', 'direction', 'channel', 'subject',
        'body', 'attachment', 'follow_up_date', 'follow_up_done', 'created_by',
    ];

    protected $casts = [
        'log_date' => 'date',
        'follow_up_date' => 'date',
        'follow_up_done' => 'boolean',
    ];

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(fn ($q2) => $q2
            ->where('subject', 'like', "%{$term}%")
            ->orWhere('body', 'like', "%{$term}%")));
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function salesContractPo(): BelongsTo
    {
        return $this->belongsTo(SalesContractPo::class, 'sales_contract_po_id');
    }
}
