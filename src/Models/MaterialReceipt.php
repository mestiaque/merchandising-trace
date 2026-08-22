<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialReceipt extends Model
{
    protected $table = 'mer_material_receipts';

    protected $fillable = ['booking_id', 'consignment_id', 'receive_date', 'item_id', 'qty', 'store_ref', 'received_by', 'remarks'];

    protected $casts = [
        'receive_date' => 'date',
        'qty' => 'decimal:4',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(MaterialBooking::class, 'booking_id');
    }

    public function consignment(): BelongsTo
    {
        return $this->belongsTo(MaterialConsignment::class, 'consignment_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
