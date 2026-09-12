<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class TnaTaskLog extends Model
{
    use HasAudit;
    protected $table = 'mer_tna_task_logs';

    public $timestamps = false;

    protected $fillable = ['tna_task_id', 'field', 'old_value', 'new_value', 'changed_by', 'changed_at', 'reason'];

    protected $casts = ['changed_at' => 'datetime'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TnaTask::class, 'tna_task_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
