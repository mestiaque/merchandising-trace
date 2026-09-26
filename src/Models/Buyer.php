<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;
use ME\MerchandisingTrace\Models\Concerns\RequiresApproval;

class Buyer extends Model
{
    use HasAudit;
    use RequiresApproval;
    use SoftDeletes;

    protected $table = 'mer_buyers';

    /** Central approval registration — see Models\Concerns\RequiresApproval. */
    public const APPROVAL_MODULE = 'merchandising.buyer';
    public const APPROVAL_PERMISSION = 'merch_buyer';

    protected $fillable = [
        'code', 'name', 'merchandiser_id', 'region', 'agent_name', 'address', 'contact_person', 'phone', 'email',
        'payment_term', 'delivery_term', 'default_aql', 'tna_template_id', 'doc_checklist_template_id', 'logo',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'default_aql' => 'decimal:2',
    ];

    /** Pickable in forms: active AND approved. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->approved();
    }

    protected function approvalRouteName(): string
    {
        return 'merchandising-trace.buyers.index';
    }

    public function merchandiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchandiser_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(BuyerContact::class, 'buyer_id');
    }

    public function styles(): HasMany
    {
        return $this->hasMany(Style::class, 'buyer_id');
    }
}
