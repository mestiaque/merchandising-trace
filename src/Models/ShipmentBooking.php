<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentBooking extends Model
{
    protected $table = 'mer_shipment_bookings';

    protected $fillable = [
        'sales_contract_po_id', 'planned_ship_date', 'forwarder_name', 'booking_no',
        'vessel_flight', 'is_short', 'short_reason', 'remarks', 'created_by',
    ];

    protected $casts = [
        'planned_ship_date' => 'date',
        'is_short' => 'boolean',
    ];

    public function salesContractPo(): BelongsTo
    {
        return $this->belongsTo(SalesContractPo::class, 'sales_contract_po_id');
    }
}
