<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy onto the OTHER package's trc_parts — feeds the part
 * picker on a style's Parts & Embellishment tab.
 */
class TrcPart extends Model
{
    protected $table = 'trc_parts';

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
