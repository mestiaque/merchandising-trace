<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;
use ME\MerchandisingTrace\Models\Concerns\RequiresApproval;

class Supplier extends Model
{
    use HasAudit;
    use RequiresApproval;
    use SoftDeletes;

    protected $table = 'mer_suppliers';

    /** Central approval registration — see Models\Concerns\RequiresApproval. */
    public const APPROVAL_MODULE = 'merchandising.supplier';
    public const APPROVAL_PERMISSION = 'merch_supplier';

    public const TYPES = ['fabric_mill', 'trims', 'accessories', 'wash', 'print', 'embroidery', 'other'];

    protected $fillable = [
        'code', 'name', 'type', 'country', 'contact', 'lead_time_days', 'payment_term', 'rating', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'approved_at' => 'datetime', 'lead_time_days' => 'integer'];

    /** Pickable in forms: active AND approved. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->approved();
    }

    protected function approvalRouteName(): string
    {
        return 'merchandising-trace.suppliers.index';
    }
}
