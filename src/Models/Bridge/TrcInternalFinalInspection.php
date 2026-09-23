<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy onto the OTHER package's trc_internal_final_inspections.
 * Never written to from this side.
 */
class TrcInternalFinalInspection extends Model
{
    protected $table = 'trc_internal_final_inspections';

    protected $casts = [
        'inspection_date' => 'date',
    ];
}
