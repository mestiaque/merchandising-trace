<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

/**
 * One line of the Open Cost Sheet, costed per DOZEN garments.
 *  - fabric:  consumption = fabric units (e.g. yds) per dozen, rate per unit
 *             → amount = consumption × rate
 *  - others:  consumption = dozens per dozen (= pieces per garment), rate
 *             per piece → amount = consumption × rate × 12
 */
class CostSheetItem extends Model
{
    use HasAudit;
    protected $table = 'mer_cost_sheet_items';

    /** Sheet sections in print order: group => [letter, section title, total-row label]. */
    public const GROUPS = [
        'fabric' => ['A', 'Shell / Body Fabrics', 'Total Fabric Cost'],
        'trims' => ['B', 'Accessories Details', 'Total Trims Cost'],
        'wash' => ['C', 'Wash', 'Total Wash Cost'],
        'stone' => ['D', 'Stone', 'Stone Cost'],
        'print' => ['E', 'Print', 'GMT Print Cost'],
        'heat_seal' => ['F', 'Heat Seal Charge', 'H/Seal Charge'],
    ];

    protected $fillable = ['cost_sheet_id', 'group', 'item_id', 'supplier_name', 'description', 'consumption', 'uom_id', 'rate', 'amount', 'remarks'];

    protected $casts = [
        'consumption' => 'decimal:4',
        'rate' => 'decimal:4',
        'amount' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::saving(function (CostSheetItem $item) {
            $item->amount = (float) $item->consumption * (float) $item->rate * self::factor($item->group);
        });
    }

    /** Multiplier from "consumption × rate" to cost per dozen. */
    public static function factor(?string $group): int
    {
        return $group === 'fabric' ? 1 : 12;
    }

    /** Consumption per garment piece (what a BOM line records). */
    public function perPieceConsumption(): float
    {
        return (float) $this->consumption * self::factor($this->group) / 12;
    }

    public function label(): string
    {
        return $this->description ?: ($this->item->name ?? '');
    }

    public function costSheet(): BelongsTo
    {
        return $this->belongsTo(CostSheet::class, 'cost_sheet_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }
}
