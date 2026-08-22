<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Proxy onto production-trace's trc_production_plans table (own package,
 * own DB connection — same database, no migration owned here). §M11: the
 * merchandising side writes into this table via the handover bridge but
 * never modifies production-trace's own code.
 */
class TrcProductionPlan extends Model
{
    use SoftDeletes;

    protected $table = 'trc_production_plans';

    protected $fillable = [
        'plan_no', 'buyer_id', 'product_id', 'season_id', 'size_group_id',
        'up_date', 'order_ref', 'status', 'remarks', 'created_by',
    ];

    protected $casts = ['up_date' => 'date'];

    public function styles(): HasMany
    {
        return $this->hasMany(TrcPlanStyle::class, 'production_plan_id')->orderBy('sort_order');
    }
}
