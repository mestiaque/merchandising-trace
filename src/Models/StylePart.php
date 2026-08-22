<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StylePart extends Model
{
    protected $table = 'mer_style_parts';

    public const EMBELLISHMENT_TYPES = ['none', 'print', 'embroidery', 'applique', 'studs_stones', 'heat_seal'];

    protected $fillable = [
        'style_id', 'trc_part_id', 'qty_per_garment', 'embellishment_type',
        'placement', 'artwork_file', 'is_critical',
    ];

    protected $casts = ['is_critical' => 'boolean'];

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function requiresEmbroidery(): bool
    {
        return in_array($this->embellishment_type, ['embroidery', 'applique'], true);
    }

    public function requiresPrint(): bool
    {
        return in_array($this->embellishment_type, ['print', 'heat_seal'], true);
    }
}
