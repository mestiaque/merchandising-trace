<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;

class Supplier extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'mer_suppliers';

    public const TYPES = ['fabric_mill', 'trims', 'accessories', 'wash', 'print', 'embroidery', 'other'];

    protected $fillable = [
        'code', 'name', 'type', 'country', 'contact', 'lead_time_days', 'payment_term', 'rating', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'lead_time_days' => 'integer'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
