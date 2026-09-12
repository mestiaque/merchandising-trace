<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class StyleMeasurementSize extends Model
{
    use HasAudit;
    protected $table = 'mer_style_measurement_sizes';

    protected $fillable = ['style_measurement_id', 'size_id', 'value'];

    protected $casts = ['value' => 'decimal:3'];

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(StyleMeasurement::class, 'style_measurement_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }
}
