<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Proxy onto the OTHER package's trc_style_parts. §M11 checklist item
 * "Style parts defined" reads it; §M11 step 5 (ProductionHandoverService::
 * syncStyleParts()) writes requires_embroidery/requires_print into it at
 * handover time, from the style's own Parts & Embellishment tab.
 */
class TrcStylePart extends Model
{
    use SoftDeletes;

    protected $table = 'trc_style_parts';

    protected $fillable = [
        'style_id', 'part_id', 'qty_per_garment', 'requires_embroidery',
        'requires_print', 'is_critical', 'created_by',
    ];

    protected $casts = [
        'requires_embroidery' => 'boolean',
        'requires_print' => 'boolean',
        'is_critical' => 'boolean',
    ];
}
