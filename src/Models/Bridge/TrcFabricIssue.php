<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy onto the OTHER package's trc_fabric_issues — fabric
 * issued to cutting for one plan line. Never written to from this side.
 */
class TrcFabricIssue extends Model
{
    protected $table = 'trc_fabric_issues';

    protected $casts = [
        'issue_date' => 'date',
    ];
}
