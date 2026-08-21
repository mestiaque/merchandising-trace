<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\IncotermFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incoterm extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mer_incoterms';

    protected $fillable = ['name', 'code', 'description', 'is_active', 'created_by'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory()
    {
        return IncotermFactory::new();
    }
}
