<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy — feeds the "Production Product" picker on a style's
 * first handover (see the migration adding mer_styles.trc_product_id).
 */
class TrcProduct extends Model
{
    protected $table = 'trc_products';

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
