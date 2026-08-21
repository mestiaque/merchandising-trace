<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\SalesContractFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesContract extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mer_sales_contracts';

    protected $fillable = ['contract_number', 'order_id', 'buyer_id', 'contract_date', 'terms', 'status', 'remarks', 'created_by'];

    protected $casts = [
        'contract_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function newFactory()
    {
        return SalesContractFactory::new();
    }
}
