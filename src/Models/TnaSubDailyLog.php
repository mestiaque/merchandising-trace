<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class TnaSubDailyLog extends Model
{
    use HasAudit;
    protected $table = 'mer_tna_sub_daily_logs';

    protected $fillable = ['tna_sub_plan_id', 'log_date', 'sending_qty', 'receiving_qty', 'remarks'];

    protected $casts = ['log_date' => 'date'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TnaSubPlan::class, 'tna_sub_plan_id');
    }

    /**
     * §4.8.1: cumulative columns, and the two hard rules — sending can't
     * exceed PO qty, receiving can't exceed sending cum.
     */
    public function sendingCumThrough(): int
    {
        return (int) $this->plan->logs()->where('log_date', '<=', $this->log_date)->sum('sending_qty');
    }

    public function receivingCumThrough(): int
    {
        return (int) $this->plan->logs()->where('log_date', '<=', $this->log_date)->sum('receiving_qty');
    }
}
