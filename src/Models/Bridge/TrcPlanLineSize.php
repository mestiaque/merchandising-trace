<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrcPlanLineSize extends Model
{
    protected $table = 'trc_plan_line_sizes';

    public $timestamps = true;

    protected $fillable = [
        'plan_line_id', 'size_id', 'order_qty', 'cut_qty', 'sewn_qty', 'finished_qty',
        'passed_qty', 'approved_qty', 'packed_qty', 'shipped_qty', 'reject_qty',
    ];

    public function planLine(): BelongsTo
    {
        return $this->belongsTo(TrcPlanLine::class, 'plan_line_id');
    }
}
