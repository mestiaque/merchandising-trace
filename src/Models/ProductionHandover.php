<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class ProductionHandover extends Model
{
    use HasAudit;
    protected $table = 'mer_production_handovers';

    public const PCD_STATUSES = ['pass', 'fail', 'overridden'];
    public const STATUSES = ['handed_over', 'rolled_back'];

    protected $fillable = [
        'sales_contract_po_id', 'plan_line_id', 'handover_date', 'handed_over_by', 'received_by',
        'pcd_status', 'override_reason', 'checklist_snapshot', 'status', 'rollback_reason',
    ];

    protected $casts = [
        'handover_date' => 'datetime',
        'checklist_snapshot' => 'array',
    ];

    public function salesContractPo(): BelongsTo
    {
        return $this->belongsTo(SalesContractPo::class, 'sales_contract_po_id');
    }

    public function handedOverBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_over_by');
    }
}
