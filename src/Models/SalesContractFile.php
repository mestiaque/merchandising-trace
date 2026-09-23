<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class SalesContractFile extends Model
{
    use HasAudit;

    protected $table = 'mer_sales_contract_files';

    protected $fillable = ['sales_contract_id', 'path', 'original_name', 'uploaded_by'];

    public function salesContract(): BelongsTo
    {
        return $this->belongsTo(SalesContract::class, 'sales_contract_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
