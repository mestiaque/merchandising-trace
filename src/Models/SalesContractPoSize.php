<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class SalesContractPoSize extends Model
{
    use HasAudit;
    protected $table = 'mer_sales_contract_po_sizes';

    protected $fillable = ['sales_contract_po_id', 'size_id', 'qty'];

    public function salesContractPo(): BelongsTo
    {
        return $this->belongsTo(SalesContractPo::class, 'sales_contract_po_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }
}
