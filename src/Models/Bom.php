<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bom extends Model
{
    use SoftDeletes;

    protected $table = 'mer_boms';

    public const STATUSES = ['draft', 'submitted', 'approved', 'revised'];

    protected $fillable = ['bom_no', 'style_id', 'version', 'status', 'approved_by', 'approved_at', 'remarks', 'created_by'];

    protected $casts = ['approved_at' => 'datetime'];

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class, 'bom_id');
    }

    /**
     * §M05 shortage view input: required qty for a given order quantity.
     */
    public function requiredQtyFor(int $orderQty): array
    {
        return $this->items->mapWithKeys(fn (BomItem $item) => [
            $item->id => $item->netConsumption() * $orderQty,
        ])->all();
    }
}
