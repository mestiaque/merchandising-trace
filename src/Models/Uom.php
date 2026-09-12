<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;

class Uom extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'mer_uoms';

    protected $fillable = ['code', 'name', 'decimal_places', 'is_active', 'created_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
