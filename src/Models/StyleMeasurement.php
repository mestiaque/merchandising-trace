<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StyleMeasurement extends Model
{
    protected $table = 'mer_style_measurements';

    protected $fillable = ['style_id', 'pom_code', 'pom_name', 'tolerance_plus', 'tolerance_minus', 'sort_order'];

    protected $casts = [
        'tolerance_plus' => 'decimal:3',
        'tolerance_minus' => 'decimal:3',
    ];

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(StyleMeasurementSize::class, 'style_measurement_id');
    }
}
