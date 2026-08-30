<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialBooking extends Model
{
    use SoftDeletes;

    protected $table = 'mer_material_bookings';

    public const TYPES = ['fabric', 'trims', 'accessory', 'packing'];
    public const STATUSES = ['draft', 'booked', 'pi_issued', 'lc_opened', 'in_transit', 'partial_received', 'received', 'closed', 'cancelled'];
    public const LC_TYPES = ['LC', 'TT', 'FOC', 'Consignment'];

    protected $fillable = [
        'booking_no', 'type', 'sales_contract_id', 'sales_contract_po_id', 'style_id', 'supplier_id',
        'mill_country', 'booking_date', 'pi_no', 'pi_date', 'pi_value', 'currency_id', 'lc_no', 'lc_date',
        'lc_value', 'lc_type', 'x_mill_date', 'expected_inhouse_date', 'status', 'remarks', 'attachment', 'created_by',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'pi_date' => 'date',
        'lc_date' => 'date',
        'x_mill_date' => 'date',
        'expected_inhouse_date' => 'date',
    ];

    public function salesContract(): BelongsTo
    {
        return $this->belongsTo(SalesContract::class, 'sales_contract_id');
    }

    public function salesContractPo(): BelongsTo
    {
        return $this->belongsTo(SalesContractPo::class, 'sales_contract_po_id');
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialBookingItem::class, 'booking_id');
    }

    public function consignments(): HasMany
    {
        return $this->hasMany(MaterialConsignment::class, 'booking_id')->orderBy('consignment_no');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(MaterialReceipt::class, 'booking_id');
    }

    public function totalBookedQty(): float
    {
        return (float) $this->items()->sum('booked_qty');
    }

    public function totalReceivedQty(): float
    {
        return (float) $this->receipts()->sum('qty');
    }

    public function balanceQty(): float
    {
        return $this->totalBookedQty() - $this->totalReceivedQty();
    }
}
