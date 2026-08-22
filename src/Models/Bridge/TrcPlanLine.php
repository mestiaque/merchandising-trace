<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrcPlanLine extends Model
{
    use SoftDeletes;

    protected $table = 'trc_plan_lines';

    protected $fillable = [
        'plan_style_id', 'sl_no', 'po_number', 'merch_order_id', 'color_id', 'fabric_id', 'fabric_code',
        'sample_rcv_status', 'trim_card_rcv_status', 'total_order_qty', 'shipped_qty',
        'sample_send_date', 'pp_meeting_date', 'plan_cut_date', 'plan_cut_close_date',
        'sewing_start_date', 'sewing_close_date', 'finishing_start_date', 'finishing_close_date',
        'fri_date', 'shipment_date', 'remarks', 'status',
    ];

    protected $casts = [
        'sample_send_date' => 'date', 'pp_meeting_date' => 'date', 'plan_cut_date' => 'date',
        'plan_cut_close_date' => 'date', 'sewing_start_date' => 'date', 'sewing_close_date' => 'date',
        'finishing_start_date' => 'date', 'finishing_close_date' => 'date', 'fri_date' => 'date',
        'shipment_date' => 'date',
    ];

    public function planStyle(): BelongsTo
    {
        return $this->belongsTo(TrcPlanStyle::class, 'plan_style_id');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(TrcPlanLineSize::class, 'plan_line_id');
    }
}
