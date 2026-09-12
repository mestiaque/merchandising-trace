<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class BuyerContact extends Model
{
    use HasAudit;
    protected $table = 'mer_buyer_contacts';

    protected $fillable = ['buyer_id', 'name', 'designation', 'email', 'phone', 'is_primary'];

    protected $casts = ['is_primary' => 'boolean'];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }
}
