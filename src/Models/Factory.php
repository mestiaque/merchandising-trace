<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;

class Factory extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'mer_factories';

    protected $fillable = ['code', 'name', 'address', 'unit_type', 'capacity_per_month', 'is_own', 'is_active'];

    protected $casts = [
        'is_own' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
