<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class SampleComment extends Model
{
    use HasAudit;
    protected $table = 'mer_sample_comments';

    protected $fillable = ['sample_id', 'comment', 'commented_by', 'comment_date', 'attachment', 'is_buyer_comment'];

    protected $casts = [
        'comment_date' => 'date',
        'is_buyer_comment' => 'boolean',
    ];

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class, 'sample_id');
    }

    public function commenter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commented_by');
    }
}
