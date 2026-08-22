<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Buyer extends Model
{
    use SoftDeletes;

    protected $table = 'mer_buyers';

    protected $fillable = [
        'code', 'name', 'merchandiser_id', 'region', 'agent_name', 'address', 'contact_person', 'phone', 'email',
        'payment_term', 'delivery_term', 'default_aql', 'tna_template_id', 'doc_checklist_template_id', 'logo',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_aql' => 'decimal:2',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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
