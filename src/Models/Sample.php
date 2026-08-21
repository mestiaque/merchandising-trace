<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\SampleFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sample extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mer_samples';

    protected $fillable = [
        'sample_number', 'buyer_id', 'style_id', 'order_id', 'sample_type', 'qty', 'size_id',
        'request_date', 'submission_date', 'approval_date', 'status', 'remarks', 'created_by',
    ];

    protected $casts = [
        'request_date'    => 'date',
        'submission_date' => 'date',
        'approval_date'   => 'date',
        'qty'             => 'integer',
    ];

    public const SAMPLE_TYPES = ['proto', 'fit', 'pp', 'size_set', 'salesman', 'photoshoot'];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'in_progress', 'sent']);
    }

    protected static function newFactory()
    {
        return SampleFactory::new();
    }
}
