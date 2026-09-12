<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasAudit;

class TnaTemplate extends Model
{
    use HasAudit;
    protected $table = 'mer_tna_templates';

    public const ANCHORS = ['shipment', 'pcd', 'order_confirm'];

    protected $fillable = ['code', 'name', 'buyer_id', 'product_type_id', 'anchor', 'is_default', 'is_active'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TnaTemplateTask::class, 'tna_template_id')->orderBy('sequence');
    }

    /**
     * §8.2: resolves the template to use for a given buyer/product type —
     * buyer+product match wins, then buyer-only, then the global default.
     */
    public static function resolveFor(?int $buyerId, ?int $productTypeId): ?self
    {
        return static::query()->active()
            ->where(fn ($q) => $q->where('buyer_id', $buyerId)->orWhereNull('buyer_id'))
            ->where(fn ($q) => $q->where('product_type_id', $productTypeId)->orWhereNull('product_type_id'))
            ->orderByRaw('buyer_id IS NULL, product_type_id IS NULL')
            ->first()
            ?? static::query()->active()->where('is_default', true)->first();
    }
}
