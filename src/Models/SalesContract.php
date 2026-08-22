<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesContract extends Model
{
    use SoftDeletes;

    protected $table = 'mer_sales_contracts';

    public const STATUSES = ['draft', 'confirmed', 'in_production', 'shipped', 'closed', 'cancelled'];

    protected $fillable = [
        'contract_no', 'buyer_id', 'season_id', 'merchandiser_id', 'factory_id', 'inquiry_id',
        'buyer_order_ref', 'contract_date', 'currency_id', 'exchange_rate', 'delivery_term', 'payment_term',
        'lc_no', 'lc_date', 'lc_value', 'lc_expiry', 'total_qty', 'total_value', 'status', 'remarks',
        'attachment', 'created_by',
    ];

    protected $casts = [
        'contract_date' => 'date',
        'lc_date' => 'date',
        'lc_expiry' => 'date',
    ];

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function merchandiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchandiser_id');
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class, 'inquiry_id');
    }

    public function pos(): HasMany
    {
        return $this->hasMany(SalesContractPo::class, 'sales_contract_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(OrderDocument::class, 'sales_contract_id');
    }

    /**
     * §M13 AC: no order can close while a mandatory document is missing.
     */
    public function hasOutstandingMandatoryDocuments(): bool
    {
        return $this->documents()->where('is_mandatory', true)->whereNotIn('status', ['uploaded', 'approved'])->exists();
    }

    /**
     * §6 Rule 1: total_qty/total_value are denormalized from the PO lines'
     * effective values — refreshed after every PO write, never hand-typed.
     */
    public function refreshTotals(): void
    {
        $pos = $this->pos()->with('sizes')->get();

        $this->update([
            'total_qty' => $pos->sum(fn (SalesContractPo $po) => $po->effectiveQty()),
            'total_value' => $pos->sum(fn (SalesContractPo $po) => $po->effectiveQty() * (float) $po->unit_price),
        ]);
    }
}
