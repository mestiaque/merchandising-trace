<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesContractPo extends Model
{
    use SoftDeletes;

    protected $table = 'mer_sales_contract_pos';

    public const STATUSES = ['pending', 'tna_created', 'pcd_passed', 'pcd_failed', 'in_production', 'shipped', 'closed'];
    public const YES_NO_NA = ['yes', 'no', 'na'];

    protected $fillable = [
        'sales_contract_id', 'style_id', 'product_type_id', 'color_id', 'wash_type_id',
        'po_no', 'po_due_date', 'po_qty', 'po_qty_revised_1', 'po_qty_revised_2',
        'unit_price', 'total_value', 'price_type', 'cost_smv', 'cm', 'fob_foc',
        'pcd_date', 'fty_possible_pcd', 'pcd_revised_1', 'pcd_revised_2',
        'shipment_date', 'fty_committed_delivery', 'shipment_revised_1', 'shipment_revised_2',
        'ship_mode_id', 'print_emb', 'emb_applique_ih', 'studs_stones_ih', 'heat_seal_ih',
        'status', 'production_plan_line_id', 'remarks',
    ];

    protected $casts = [
        'po_due_date' => 'date',
        'pcd_date' => 'date',
        'fty_possible_pcd' => 'date',
        'pcd_revised_1' => 'date',
        'pcd_revised_2' => 'date',
        'shipment_date' => 'date',
        'fty_committed_delivery' => 'date',
        'shipment_revised_1' => 'date',
        'shipment_revised_2' => 'date',
    ];

    public function salesContract(): BelongsTo
    {
        return $this->belongsTo(SalesContract::class, 'sales_contract_id');
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function washType(): BelongsTo
    {
        return $this->belongsTo(WashType::class, 'wash_type_id');
    }

    public function shipMode(): BelongsTo
    {
        return $this->belongsTo(ShipMode::class, 'ship_mode_id');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(SalesContractPoSize::class, 'sales_contract_po_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(SalesContractPoRevision::class, 'sales_contract_po_id')->latest('changed_at');
    }

    public function shipmentBookings(): HasMany
    {
        return $this->hasMany(ShipmentBooking::class, 'sales_contract_po_id');
    }

    public function tnaPlan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TnaPlan::class, 'sales_contract_po_id');
    }

    /**
     * §6 Rule 1 — the effective-value rule: everywhere qty/PCD/shipment is
     * used, resolve the latest non-null revision. Never read po_qty,
     * pcd_date or shipment_date directly outside these three helpers.
     */
    public function effectiveQty(): int
    {
        return (int) ($this->po_qty_revised_2 ?? $this->po_qty_revised_1 ?? $this->po_qty ?? 0);
    }

    public function effectivePcd(): ?\Illuminate\Support\Carbon
    {
        return $this->pcd_revised_2 ?? $this->pcd_revised_1 ?? $this->pcd_date;
    }

    public function effectiveShipment(): ?\Illuminate\Support\Carbon
    {
        return $this->shipment_revised_2 ?? $this->shipment_revised_1 ?? $this->shipment_date;
    }

    /**
     * §6 Rule 4: must hold before handover (P13) is allowed.
     */
    public function sizeQtyMatchesEffectiveQty(): bool
    {
        return (int) $this->sizes()->sum('qty') === $this->effectiveQty();
    }
}
