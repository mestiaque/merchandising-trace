<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy onto the OTHER package's trc_cuttings. Never written to
 * from this side.
 */
class TrcCutting extends Model
{
    protected $table = 'trc_cuttings';

    protected $casts = [
        'cut_date' => 'date',
    ];
}
