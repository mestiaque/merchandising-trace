<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesContractPoRevision extends Model
{
    protected $table = 'mer_sales_contract_po_revisions';

    public $timestamps = false;

    protected $fillable = ['sales_contract_po_id', 'field', 'old_value', 'new_value', 'reason', 'changed_by', 'changed_at'];

    protected $casts = ['changed_at' => 'datetime'];

    public function salesContractPo(): BelongsTo
    {
        return $this->belongsTo(SalesContractPo::class, 'sales_contract_po_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
