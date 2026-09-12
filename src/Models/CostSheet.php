<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;

class CostSheet extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'mer_cost_sheets';

    public const STATUSES = ['draft', 'submitted', 'approved', 'rejected', 'revised'];
    public const PRICE_TYPES = ['FOB', 'CM', 'CMT', 'CIF', 'DDP', 'FOC'];

    protected $fillable = [
        'cost_sheet_no', 'style_id', 'buyer_id', 'version', 'currency_id', 'exchange_rate', 'order_qty',
        'smv', 'cm_minute_rate', 'efficiency_percent', 'fabric_cost', 'trims_cost', 'accessories_cost',
        'print_emb_cost', 'wash_cost', 'cm_cost', 'commercial_cost', 'freight_cost', 'testing_cost',
        'overhead_cost', 'total_cost', 'profit_percent', 'profit_amount', 'offer_price', 'buyer_target_price',
        'final_price', 'price_type', 'status', 'prepared_by', 'approved_by', 'approved_at', 'remarks',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CostSheetItem::class, 'cost_sheet_id');
    }

    /**
     * §M06 formulas — computed on demand from current inputs, never trusted
     * from stored totals alone (recompute() below persists the result).
     */
    public function calcCm(): float
    {
        if (! $this->smv || ! $this->cm_minute_rate || ! $this->efficiency_percent) {
            return 0.0;
        }

        return ((float) $this->smv / ((float) $this->efficiency_percent / 100)) * (float) $this->cm_minute_rate;
    }

    public function calcTotalCost(): float
    {
        return (float) $this->fabric_cost + (float) $this->trims_cost + (float) $this->accessories_cost
            + (float) $this->print_emb_cost + (float) $this->wash_cost + $this->calcCm()
            + (float) $this->commercial_cost + (float) $this->freight_cost + (float) $this->testing_cost
            + (float) $this->overhead_cost;
    }

    public function calcOfferPrice(): float
    {
        $total = $this->calcTotalCost();
        $profitFraction = (float) $this->profit_percent / 100;

        if ($profitFraction >= 1) {
            return 0.0;
        }

        return $total / (1 - $profitFraction);
    }

    public function calcMarginPercent(): float
    {
        $final = (float) ($this->final_price ?: $this->offer_price);
        if (! $final) {
            return 0.0;
        }

        return (($final - $this->calcTotalCost()) / $final) * 100;
    }

    /**
     * Recomputes group costs from line items, then CM/total/offer, and
     * persists — call after any item add/remove/edit.
     */
    public function recompute(): void
    {
        $sums = $this->items()->selectRaw('`group`, SUM(amount) as total')->groupBy('group')->pluck('total', 'group');

        $this->fabric_cost = $sums['fabric'] ?? 0;
        $this->trims_cost = $sums['trims'] ?? 0;
        $this->accessories_cost = $sums['accessories'] ?? 0;
        $this->commercial_cost = $sums['commercial'] ?? ($this->commercial_cost ?? 0);

        $this->cm_cost = $this->calcCm();
        $this->total_cost = $this->calcTotalCost();
        $this->profit_amount = $this->total_cost * ((float) $this->profit_percent / 100);
        $this->offer_price = $this->calcOfferPrice();
        $this->save();
    }
}
