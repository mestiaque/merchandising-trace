<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

/**
 * One budget-vs-actual line of a Post Cost Sheet, costed per DOZEN with the
 * same rule as the pre-cost (CostSheetItem::factor): fabric = cons × rate,
 * other sections = cons × rate × 12.
 */
class PostCostSheetItem extends Model
{
    use HasAudit;

    protected $table = 'mer_post_cost_sheet_items';

    public const SOURCES = ['pre_cost' => 'Pre-cost', 'booking' => 'Booking', 'manual' => 'Manual'];

    protected $fillable = [
        'post_cost_sheet_id', 'group', 'item_id', 'description', 'supplier_name', 'uom_id',
        'budget_consumption', 'budget_rate', 'budget_amount',
        'actual_consumption', 'actual_rate', 'actual_amount', 'source', 'remarks',
    ];

    protected $casts = [
        'budget_consumption' => 'decimal:4', 'budget_rate' => 'decimal:4', 'budget_amount' => 'decimal:4',
        'actual_consumption' => 'decimal:4', 'actual_rate' => 'decimal:4', 'actual_amount' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::saving(function (PostCostSheetItem $line) {
            $factor = CostSheetItem::factor($line->group);
            $line->budget_amount = (float) $line->budget_consumption * (float) $line->budget_rate * $factor;
            $line->actual_amount = (float) $line->actual_consumption * (float) $line->actual_rate * $factor;
        });
    }

    /** Actual − budget, per dozen (positive = over budget). */
    public function variance(): float
    {
        return (float) $this->actual_amount - (float) $this->budget_amount;
    }

    public function label(): string
    {
        return $this->description ?: ($this->item->name ?? '');
    }

    public function postCostSheet(): BelongsTo
    {
        return $this->belongsTo(PostCostSheet::class, 'post_cost_sheet_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }
}
