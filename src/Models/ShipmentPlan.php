<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\ShipmentPlanFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentPlan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mer_shipment_plans';

    protected $fillable = [
        'plan_number', 'order_id', 'planned_date', 'actual_date', 'planned_qty',
        'destination_port', 'mode', 'status', 'remarks', 'created_by',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date'  => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function newFactory()
    {
        return ShipmentPlanFactory::new();
    }
}
