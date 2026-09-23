<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy onto the OTHER package's trc_garment_process_jobs (wash /
 * print jobs at the garment level). Never written to from this side.
 */
class TrcGarmentProcessJob extends Model
{
    protected $table = 'trc_garment_process_jobs';

    protected $casts = [
        'issue_date' => 'date',
        'expected_return_date' => 'date',
        'receive_date' => 'date',
    ];
}
