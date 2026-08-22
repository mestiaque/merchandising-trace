<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\SampleFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sample extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mer_samples';

    protected $fillable = [
        'sample_number', 'buyer_id', 'style_id', 'order_id', 'season_id', 'merchandiser_id',
        'sample_type', 'sample_type_id', 'qty', 'size_id', 'size_ref', 'color_ref',
        'request_date', 'required_date', 'submission_date', 'courier_name', 'tracking_no',
        'approval_date', 'status', 'remarks', 'buyer_comments', 'attachment',
        'revision_no', 'parent_sample_id', 'created_by',
    ];

    protected $casts = [
        'request_date'    => 'date',
        'required_date'   => 'date',
        'submission_date' => 'date',
        'approval_date'   => 'date',
        'qty'             => 'integer',
        'revision_no'     => 'integer',
    ];

    // requested, in_progress, submitted, approved, rejected, resubmit, cancelled
    public const STATUSES = ['requested', 'in_progress', 'submitted', 'approved', 'rejected', 'resubmit', 'cancelled'];

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

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function merchandiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchandiser_id');
    }

    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class, 'sample_type_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentSample(): BelongsTo
    {
        return $this->belongsTo(Sample::class, 'parent_sample_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Sample::class, 'parent_sample_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SampleComment::class, 'sample_id')->latest('comment_date');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['requested', 'in_progress', 'submitted', 'resubmit']);
    }

    /**
     * §M04: submission after required_date flags red on the board.
     */
    public function isLateSubmission(): bool
    {
        return $this->required_date !== null
            && $this->submission_date !== null
            && $this->submission_date->gt($this->required_date);
    }
}
