<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mer_orders';

    protected $fillable = [
        'po_number', 'buyer_id', 'style_id', 'description', 'order_qty', 'delivery_date',
        'price', 'currency', 'status', 'remarks', 'created_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'price'         => 'decimal:4',
        'order_qty'     => 'integer',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function salesContracts(): HasMany
    {
        return $this->hasMany(SalesContract::class, 'order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * order_qty is a persisted denormalization of sum(items.qty), refreshed
     * after every breakdown-line write so list/report queries never need to
     * join+sum on every read.
     */
    public function refreshOrderQty(): void
    {
        $this->update(['order_qty' => $this->items()->sum('qty')]);
    }

    public function bom(): ?Bom
    {
        return Bom::query()->where('style_id', $this->style_id)->where('status', 'active')->latest('version')->first()
            ?? Bom::query()->where('style_id', $this->style_id)->latest('version')->first();
    }

    protected static function newFactory()
    {
        return OrderFactory::new();
    }
}
