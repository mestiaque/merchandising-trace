<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\CostingFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Costing extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mer_costings';

    protected $fillable = [
        'costing_number', 'order_id', 'type', 'fob_price', 'fabric_cost', 'trim_cost',
        'wash_cost', 'embroidery_print_cost', 'overhead_cost', 'status', 'remarks', 'created_by',
    ];

    protected $casts = [
        'fob_price'             => 'decimal:4',
        'fabric_cost'           => 'decimal:4',
        'trim_cost'             => 'decimal:4',
        'wash_cost'             => 'decimal:4',
        'embroidery_print_cost' => 'decimal:4',
        'overhead_cost'         => 'decimal:4',
    ];

    public const TYPES = ['pre' => 'Pre Costing', 'actual' => 'Actual Costing'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalCost(): float
    {
        return (float) $this->fabric_cost + (float) $this->trim_cost + (float) $this->wash_cost
            + (float) $this->embroidery_print_cost + (float) $this->overhead_cost;
    }

    public function cmAmount(): float
    {
        return (float) $this->fob_price - $this->totalCost();
    }

    public function profitMarginPercent(): float
    {
        return (float) $this->fob_price > 0 ? round($this->cmAmount() / (float) $this->fob_price * 100, 2) : 0.0;
    }

    protected static function newFactory()
    {
        return CostingFactory::new();
    }
}
