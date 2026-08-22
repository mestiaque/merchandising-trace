<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StyleImage extends Model
{
    protected $table = 'mer_style_images';

    protected $fillable = ['style_id', 'path', 'type', 'caption'];

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }
}
