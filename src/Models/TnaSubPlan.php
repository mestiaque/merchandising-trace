<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TnaSubPlan extends Model
{
    protected $table = 'mer_tna_sub_plans';

    public const PROCESS_TYPES = ['embroidery', 'print', 'after_wash'];
    public const STATUSES = ['open', 'running', 'completed', 'closed'];

    protected $fillable = [
        'sub_no', 'sales_contract_po_id', 'process_type', 'emb_print_type', 'required_psd', 'required_pfd',
        'required_qty_per_day', 'plant_name', 'vendor_id', 'po_qty', 'status',
    ];

    protected $casts = [
        'required_psd' => 'date',
        'required_pfd' => 'date',
    ];

    public function salesContractPo(): BelongsTo
    {
        return $this->belongsTo(SalesContractPo::class, 'sales_contract_po_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'vendor_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TnaSubDailyLog::class, 'tna_sub_plan_id')->orderBy('log_date');
    }

    public function totalSent(): int
    {
        return (int) $this->logs()->sum('sending_qty');
    }

    public function totalReceived(): int
    {
        return (int) $this->logs()->sum('receiving_qty');
    }

    /**
     * §4.8.1: balance_qty = po_qty - receiving_cum — computed, never typed.
     */
    public function balanceQty(): int
    {
        return $this->po_qty - $this->totalReceived();
    }

    public function isBehindSchedule(): bool
    {
        if (! $this->required_qty_per_day || ! $this->required_psd) {
            return false;
        }

        $daysElapsed = max(0, now()->diffInDays($this->required_psd, false) * -1);
        $expected = $daysElapsed * $this->required_qty_per_day;

        return $this->totalReceived() < $expected;
    }
}
