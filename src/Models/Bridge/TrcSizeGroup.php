<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy — feeds the "Size Group" picker on a style's first
 * handover (see the migration adding mer_styles.trc_size_group_id).
 */
class TrcSizeGroup extends Model
{
    protected $table = 'trc_size_groups';

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
