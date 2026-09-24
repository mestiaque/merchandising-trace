<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;

/**
 * Two kinds of BOM (bom_type):
 *  - file:   buyer-provided — we store and serve their PDF (bom_file, on
 *            Storage's public disk, same convention as Style::tech_pack_file);
 *  - manual: built here line by line (items()).
 */
class Bom extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'mer_boms';

    public const STATUSES = ['draft', 'submitted', 'approved', 'revised'];
    public const TYPES = ['file' => 'Upload Buyer PDF', 'manual' => 'Create BOM'];

    protected $fillable = ['bom_no', 'style_id', 'version', 'bom_type', 'status', 'approved_by', 'approved_at', 'remarks', 'bom_file', 'created_by'];

    protected $casts = ['approved_at' => 'datetime'];

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class, 'bom_id');
    }

    public function isManual(): bool
    {
        return $this->bom_type === 'manual';
    }
}
