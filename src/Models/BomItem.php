<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomItem extends Model
{
    protected $table = 'mer_bom_items';

    protected $fillable = [
        'bom_id', 'item_type', 'material_name', 'unit_id', 'consumption', 'waste_percent', 'remarks',
    ];

    protected $casts = [
        'consumption'   => 'decimal:4',
        'waste_percent' => 'decimal:2',
    ];

    public const ITEM_TYPES = [
        'fabric', 'thread', 'button', 'label', 'hang_tag', 'poly', 'carton', 'accessories', 'other',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'unit_id');
    }
}
