<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mer_currencies';

    protected $fillable = ['name', 'code', 'symbol', 'exchange_rate', 'is_active', 'created_by'];

    protected $casts = [
        'is_active' => 'boolean',
        'exchange_rate' => 'decimal:4',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory()
    {
        return CurrencyFactory::new();
    }
}
