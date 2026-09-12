<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class ExchangeRate extends Model
{
    use HasAudit;
    protected $table = 'mer_exchange_rates';

    protected $fillable = ['currency_id', 'rate', 'effective_date'];

    protected $casts = [
        'rate' => 'decimal:4',
        'effective_date' => 'date',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
