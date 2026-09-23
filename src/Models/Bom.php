<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;

/**
 * The BOM is buyer-provided — we don't build it line-by-line, we just store
 * and serve the PDF the buyer hands over (bom_file, on Storage's public
 * disk, same convention as Style::tech_pack_file / sales_contract_file).
 */
class Bom extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'mer_boms';

    public const STATUSES = ['draft', 'submitted', 'approved', 'revised'];

    protected $fillable = ['bom_no', 'style_id', 'version', 'status', 'approved_by', 'approved_at', 'remarks', 'bom_file', 'created_by'];

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
}
