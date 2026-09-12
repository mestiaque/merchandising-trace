<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;

class RiskAssessment extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'mer_risk_assessments';

    protected $fillable = [
        'style_id', 'season_id', 'collection_name', 'category',
        'sewing_factory_id', 'print_factory_id', 'embroidery_factory_id', 'wash_factory_id',
        'design_risk', 'materials_risk', 'components_risk', 'process_risk',
        'is_active', 'created_by',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function sewingFactory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'sewing_factory_id');
    }

    public function printFactory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'print_factory_id');
    }

    public function embroideryFactory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'embroidery_factory_id');
    }

    public function washFactory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'wash_factory_id');
    }
}
