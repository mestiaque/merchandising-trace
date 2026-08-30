<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Style extends Model
{
    use SoftDeletes;

    protected $table = 'mer_styles';

    public const DEVELOPMENT_STATUSES = ['new', 'in_development', 'sample_stage', 'approved', 'in_production', 'closed'];

    protected $fillable = [
        'style_no', 'name', 'description', 'image', 'buyer_id', 'inquiry_id', 'season_id', 'merchandiser_id',
        'wash_type_id', 'product_type_id', 'trc_product_id', 'trc_size_group_id', 'smv', 'cost_smv', 'target_cm',
        'fabric_description', 'tech_pack_file', 'artwork_file', 'size_chart_file', 'development_status',
        'requires_dev_sample', 'fabric_sourced_by',
        'is_repeat', 'parent_style_id', 'is_active', 'created_by',
    ];

    public const FABRIC_SOURCED_BY = ['self', 'buyer'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_repeat' => 'boolean',
        'requires_dev_sample' => 'boolean',
        'smv' => 'decimal:2',
        'cost_smv' => 'decimal:2',
        'target_cm' => 'decimal:4',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class, 'inquiry_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function merchandiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchandiser_id');
    }

    public function washType(): BelongsTo
    {
        return $this->belongsTo(WashType::class, 'wash_type_id');
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function parentStyle(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'parent_style_id');
    }

    public function repeats(): HasMany
    {
        return $this->hasMany(Style::class, 'parent_style_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(StyleImage::class, 'style_id');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(StyleMeasurement::class, 'style_id')->orderBy('sort_order');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(StylePart::class, 'style_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(StyleOperation::class, 'style_id')->orderBy('sequence');
    }
}
