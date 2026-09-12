<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class PoProductionProgress extends Model
{
    use HasAudit;
    protected $table = 'mer_po_production_progress';

    protected $fillable = [
        'sales_contract_po_id', 'order_qty', 'cut_qty', 'sewn_qty', 'finished_qty',
        'packed_qty', 'shipped_qty', 'reject_qty', 'dhu', 'synced_at',
    ];

    protected $casts = [
        'dhu' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    public function salesContractPo(): BelongsTo
    {
        return $this->belongsTo(SalesContractPo::class, 'sales_contract_po_id');
    }

    public function cutPercent(): float
    {
        return $this->order_qty > 0 ? round($this->cut_qty / $this->order_qty * 100, 1) : 0.0;
    }

    public function sewnPercent(): float
    {
        return $this->order_qty > 0 ? round($this->sewn_qty / $this->order_qty * 100, 1) : 0.0;
    }

    public function finishedPercent(): float
    {
        return $this->order_qty > 0 ? round($this->finished_qty / $this->order_qty * 100, 1) : 0.0;
    }

    public function packedPercent(): float
    {
        return $this->order_qty > 0 ? round($this->packed_qty / $this->order_qty * 100, 1) : 0.0;
    }

    public function shippedPercent(): float
    {
        return $this->order_qty > 0 ? round($this->shipped_qty / $this->order_qty * 100, 1) : 0.0;
    }

    public function wipQty(): int
    {
        return max(0, $this->cut_qty - $this->finished_qty);
    }

    public function currentStage(): string
    {
        return match (true) {
            $this->order_qty > 0 && $this->shipped_qty >= $this->order_qty => 'Shipped',
            $this->packed_qty > 0 => 'Packing',
            $this->finished_qty > 0 => 'Finishing',
            $this->sewn_qty > 0 => 'Sewing',
            $this->cut_qty > 0 => 'Cutting',
            default => 'Not Started',
        };
    }
}
