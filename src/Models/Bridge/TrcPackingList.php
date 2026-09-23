<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy onto the OTHER package's trc_packing_lists. Never written
 * to from this side.
 */
class TrcPackingList extends Model
{
    protected $table = 'trc_packing_lists';

    protected $casts = [
        'pack_date' => 'date',
    ];
}
