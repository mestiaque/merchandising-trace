<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy — §M11 checklist item "Style parts defined" reads this
 * production-trace table but the bridge never writes to it.
 */
class TrcStylePart extends Model
{
    protected $table = 'trc_style_parts';
}
