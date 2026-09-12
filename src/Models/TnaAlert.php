<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class TnaAlert extends Model
{
    use HasAudit;
    protected $table = 'mer_tna_alerts';

    public const TYPES = ['due_soon', 'overdue', 'blocked_pcd'];

    protected $fillable = ['tna_task_id', 'alert_type', 'alert_date', 'notified_to', 'is_read'];

    protected $casts = [
        'alert_date' => 'date',
        'is_read' => 'boolean',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TnaTask::class, 'tna_task_id');
    }

    public function notifiedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notified_to');
    }
}
