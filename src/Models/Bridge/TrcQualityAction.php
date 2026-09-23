<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy onto the OTHER package's trc_quality_actions (CAPA log).
 * Never written to from this side.
 */
class TrcQualityAction extends Model
{
    protected $table = 'trc_quality_actions';

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
    ];
}
