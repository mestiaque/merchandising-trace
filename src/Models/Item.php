<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = 'mer_items';

    public const TYPES = ['fabric', 'trim', 'accessory', 'packing'];

    protected $fillable = [
        'code', 'name', 'category_id', 'type', 'uom_id', 'default_supplier_id',
        'default_price', 'consumption_uom', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_price' => 'decimal:4',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    public function defaultSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }
}
