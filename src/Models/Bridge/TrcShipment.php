<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy onto the OTHER package's trc_shipments — §M12's "actual"
 * side of the plan-vs-actual board. Never written to from this side.
 */
class TrcShipment extends Model
{
    protected $table = 'trc_shipments';

    protected $casts = [
        'ex_factory_date' => 'date',
        'shipment_date' => 'date',
    ];
}
