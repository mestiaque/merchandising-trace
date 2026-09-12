<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class FabricConsumption extends Model
{
    use HasAudit;
    protected $table = 'mer_fabric_consumptions';

    protected $fillable = ['style_id', 'color_id', 'item_id', 'yy', 'marker_efficiency', 'gsm', 'width', 'calculated_by', 'method'];

    protected $casts = [
        'yy' => 'decimal:4',
        'marker_efficiency' => 'decimal:2',
        'gsm' => 'decimal:2',
        'width' => 'decimal:2',
    ];

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    /**
     * §4.5: Fabric Requirement = YY × PO Qty × (1 + wastage%).
     */
    public function requirementFor(int $poQty, float $wastagePercent = 0): float
    {
        return (float) $this->yy * $poQty * (1 + $wastagePercent / 100);
    }
}
