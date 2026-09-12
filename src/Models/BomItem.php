<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class BomItem extends Model
{
    use HasAudit;
    protected $table = 'mer_bom_items';

    protected $fillable = [
        'bom_id', 'item_id', 'item_type', 'color_id', 'size_id', 'part_name', 'consumption',
        'uom_id', 'wastage_percent', 'rate', 'currency_id', 'supplier_id', 'lead_time_days', 'remarks',
    ];

    protected $casts = [
        'consumption' => 'decimal:4',
        'wastage_percent' => 'decimal:2',
        'rate' => 'decimal:4',
        'lead_time_days' => 'integer',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * §4.5: net_consumption = consumption × (1 + wastage%/100), always
     * computed — never stored, so it can't drift from its inputs.
     */
    public function netConsumption(): float
    {
        return (float) $this->consumption * (1 + (float) $this->wastage_percent / 100);
    }
}
