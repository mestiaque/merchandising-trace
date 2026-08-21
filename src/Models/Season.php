<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\SeasonFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Season extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mer_seasons';

    protected $fillable = ['name', 'code', 'start_date', 'end_date', 'is_active', 'created_by'];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory()
    {
        return SeasonFactory::new();
    }
}
