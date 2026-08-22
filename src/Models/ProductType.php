<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductType extends Model
{
    use SoftDeletes;

    protected $table = 'mer_product_types';

    protected $fillable = ['code', 'name', 'category', 'default_smv', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'default_smv' => 'decimal:2',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
