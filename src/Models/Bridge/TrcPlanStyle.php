<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrcPlanStyle extends Model
{
    use SoftDeletes;

    protected $table = 'trc_plan_styles';

    protected $fillable = [
        'production_plan_id', 'style_id', 'style_name', 'style_no',
        'styling_confirmed', 'styling_note', 'sort_order',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TrcProductionPlan::class, 'production_plan_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(TrcPlanLine::class, 'plan_style_id')->orderBy('sl_no');
    }
}
