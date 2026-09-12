<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAudit;

class SampleType extends Model
{
    use HasAudit;
    protected $table = 'mer_sample_types';

    protected $fillable = ['code', 'name', 'sequence', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
