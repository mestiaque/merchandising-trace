<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use ME\MerchandisingTrace\Support\Scopes\ScopedToMerchandiser;

class Sample extends Model
{
    use SoftDeletes;

    protected $table = 'mer_samples';

    public const STATUSES = ['requested', 'in_progress', 'submitted', 'approved', 'rejected', 'resubmit', 'cancelled'];

    protected static function booted(): void
    {
        static::addGlobalScope(new ScopedToMerchandiser());
    }

    protected $fillable = [
        'sample_no', 'style_id', 'buyer_id', 'season_id', 'sample_type_id', 'merchandiser_id', 'order_id',
        'request_date', 'required_date', 'qty', 'size_ref', 'color_ref', 'submit_date', 'courier_name',
        'tracking_no', 'approval_date', 'status', 'remarks', 'buyer_comments', 'attachment',
        'revision_no', 'parent_sample_id', 'created_by',
    ];

    protected $casts = [
        'request_date' => 'date',
        'required_date' => 'date',
        'submit_date' => 'date',
        'approval_date' => 'date',
        'qty' => 'integer',
        'revision_no' => 'integer',
    ];

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['requested', 'in_progress', 'submitted', 'resubmit']);
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class, 'sample_type_id');
    }

    public function merchandiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchandiser_id');
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

    /**
     * §M04: submission after required_date flags red on the board.
     */
    public function isLateSubmission(): bool
    {
        return $this->required_date !== null
            && $this->submit_date !== null
            && $this->submit_date->gt($this->required_date);
    }
}
