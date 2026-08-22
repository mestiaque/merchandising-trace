<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerContact extends Model
{
    protected $table = 'mer_buyer_contacts';

    protected $fillable = ['buyer_id', 'name', 'designation', 'email', 'phone', 'is_primary'];

    protected $casts = ['is_primary' => 'boolean'];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }
}
