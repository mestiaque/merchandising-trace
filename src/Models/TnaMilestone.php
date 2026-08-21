<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\TnaMilestoneFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TnaMilestone extends Model
{
    use HasFactory;

    protected $table = 'mer_tna_milestones';

    protected $fillable = ['order_id', 'milestone_name', 'planned_date', 'actual_date', 'status', 'is_escalated', 'remarks', 'created_by'];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date'  => 'date',
        'is_escalated' => 'boolean',
    ];

    public const MILESTONES = [
        'Fabric Booking', 'Fabric In-house', 'Trim Booking', 'Trim In-house',
        'Sample Approval', 'Cutting Start', 'Sewing Start', 'Ex-Factory', 'Shipment',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDelayed(): bool
    {
        return $this->status !== 'completed' && $this->planned_date->isPast();
    }

    public function scopeDelayed(Builder $query): Builder
    {
        return $query->where('status', '!=', 'completed')->whereDate('planned_date', '<', now()->toDateString());
    }

    protected static function newFactory()
    {
        return TnaMilestoneFactory::new();
    }
}
