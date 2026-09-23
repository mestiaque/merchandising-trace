<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy onto the OTHER package's trc_buyer_inspections. Never
 * written to from this side.
 */
class TrcBuyerInspection extends Model
{
    protected $table = 'trc_buyer_inspections';

    protected $casts = [
        'inspection_date' => 'date',
    ];
}
