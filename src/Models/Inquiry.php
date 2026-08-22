<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inquiry extends Model
{
    use SoftDeletes;

    protected $table = 'mer_inquiries';

    protected $fillable = [
        'inquiry_no', 'inquiry_given_date', 'buyer_id', 'season_id', 'merchandiser_id', 'factory_id',
        'order_confirmation_due_date', 'product_type_id', 'description', 'target_qty', 'target_price',
        'target_ship_date', 'status', 'lost_reason', 'remarks', 'created_by',
    ];

    protected $casts = [
        'inquiry_given_date' => 'date',
        'order_confirmation_due_date' => 'date',
        'target_ship_date' => 'date',
        'target_price' => 'decimal:4',
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

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InquiryItem::class, 'inquiry_id');
    }

    public function styles(): HasMany
    {
        return $this->hasMany(Style::class, 'inquiry_id');
    }

    /**
     * Ageing rule (§M02): flagged once past due while still open — used to
     * badge the inquiry list, not to change status automatically.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'open'
            && $this->order_confirmation_due_date !== null
            && $this->order_confirmation_due_date->isPast();
    }
}
