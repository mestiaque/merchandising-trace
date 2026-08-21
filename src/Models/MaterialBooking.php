<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\MaterialBookingFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialBooking extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mer_material_bookings';

    protected $fillable = [
        'booking_number', 'order_id', 'supplier_id', 'material_type', 'material_name',
        'qty', 'unit_id', 'booking_date', 'expected_date', 'status', 'remarks', 'created_by',
    ];

    protected $casts = [
        'qty'           => 'decimal:4',
        'booking_date'  => 'date',
        'expected_date' => 'date',
    ];

    public const MATERIAL_TYPES = ['fabric', 'trim', 'yarn', 'accessories'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'unit_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function newFactory()
    {
        return MaterialBookingFactory::new();
    }
}
