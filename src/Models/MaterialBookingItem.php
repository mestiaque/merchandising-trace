<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class MaterialBookingItem extends Model
{
    use HasAudit;
    protected $table = 'mer_material_booking_items';

    protected $fillable = ['booking_id', 'item_id', 'color_id', 'description', 'booked_qty', 'uom_id', 'rate', 'amount'];

    protected $casts = [
        'booked_qty' => 'decimal:4',
        'rate' => 'decimal:4',
        'amount' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::saving(function (MaterialBookingItem $item) {
            $item->amount = (float) $item->booked_qty * (float) $item->rate;
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(MaterialBooking::class, 'booking_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    public function receivedQty(): float
    {
        return (float) MaterialReceipt::query()
            ->where('booking_id', $this->booking_id)
            ->where('item_id', $this->item_id)
            ->sum('qty');
    }

    public function balanceQty(): float
    {
        return (float) $this->booked_qty - $this->receivedQty();
    }
}
