<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class StyleOperation extends Model
{
    use HasAudit;
    protected $table = 'mer_style_operations';

    protected $fillable = ['style_id', 'operation_name', 'machine_type', 'smv', 'sequence'];

    protected $casts = ['smv' => 'decimal:4'];

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }
}
