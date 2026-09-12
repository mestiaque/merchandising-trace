<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class MaterialConsignment extends Model
{
    use HasAudit;
    protected $table = 'mer_material_consignments';

    public const STATUSES = ['pending', 'shipped', 'received', 'short'];

    protected $fillable = [
        'booking_id', 'consignment_no', 'planned_date', 'actual_date', 'planned_qty',
        'received_qty', 'challan_no', 'invoice_no', 'status', 'remarks',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date' => 'date',
        'planned_qty' => 'decimal:4',
        'received_qty' => 'decimal:4',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(MaterialBooking::class, 'booking_id');
    }
}
