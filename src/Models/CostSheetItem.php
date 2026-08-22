<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostSheetItem extends Model
{
    protected $table = 'mer_cost_sheet_items';

    public const GROUPS = ['fabric', 'trims', 'accessories', 'process', 'commercial'];

    protected $fillable = ['cost_sheet_id', 'group', 'item_id', 'description', 'consumption', 'uom_id', 'rate', 'amount', 'remarks'];

    protected $casts = [
        'consumption' => 'decimal:4',
        'rate' => 'decimal:4',
        'amount' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::saving(function (CostSheetItem $item) {
            $item->amount = (float) $item->consumption * (float) $item->rate;
        });
    }

    public function costSheet(): BelongsTo
    {
        return $this->belongsTo(CostSheet::class, 'cost_sheet_id');
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
