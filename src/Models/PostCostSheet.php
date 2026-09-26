<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;

/**
 * Post Cost Sheet: the approved pre-cost (budget) against what the order
 * actually cost. Lines per dozen (PostCostSheetItem); header money per piece.
 *
 *   cost / pc      = sections A–F / 12 + CM + commercial + other
 *   budget total   = budget cost / pc × order qty
 *   actual total   = actual cost / pc × shipped qty (order qty until shipped)
 *   revenue        = selling price × the same qty;  profit = revenue − actual total
 */
class PostCostSheet extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'mer_post_cost_sheets';

    public const STATUSES = ['draft', 'approved'];

    protected $fillable = [
        'post_cost_no', 'cost_sheet_id', 'sales_contract_id', 'style_id', 'buyer_id', 'style_ref', 'garment_description',
        'currency_id', 'costing_date', 'order_qty', 'shipped_qty', 'selling_price',
        'budget_cm_cost', 'budget_commercial_cost', 'budget_other_cost', 'budget_smv',
        'actual_smv', 'actual_cm_cost', 'actual_commercial_percent', 'actual_other_cost',
        'budget_total_cost', 'actual_total_cost', 'status', 'prepared_by', 'approved_by', 'approved_at', 'remarks',
    ];

    protected $casts = [
        'costing_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function costSheet(): BelongsTo
    {
        return $this->belongsTo(CostSheet::class, 'cost_sheet_id');
    }

    public function salesContract(): BelongsTo
    {
        return $this->belongsTo(SalesContract::class, 'sales_contract_id');
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PostCostSheetItem::class, 'post_cost_sheet_id')->orderBy('id');
    }

    public function styleLabel(): string
    {
        return $this->style->style_no ?? $this->style_ref ?? '-';
    }

    /** Garments the actual cost is spread over: shipped, or ordered until shipment. */
    public function actualQtyBasis(): int
    {
        return (int) ($this->shipped_qty ?: $this->order_qty);
    }

    /**
     * Every figure on the sheet. Money per dozen ("dz") and per piece ("pc");
     * each row carries budget ("b"), actual ("a") and variance ("v" = a − b).
     */
    public function summary(): array
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();
        $row = fn (float $bDz, float $aDz) => [
            'b' => ['dz' => $bDz, 'pc' => $bDz / 12],
            'a' => ['dz' => $aDz, 'pc' => $aDz / 12],
            'v' => ['dz' => $aDz - $bDz, 'pc' => ($aDz - $bDz) / 12, 'pct' => $bDz > 0 ? ($aDz - $bDz) / $bDz * 100 : null],
        ];

        $groups = [];
        $bMat = $aMat = 0.0;
        foreach (array_keys(CostSheetItem::GROUPS) as $group) {
            $lines = $items->where('group', $group);
            $b = (float) $lines->sum('budget_amount');
            $a = (float) $lines->sum('actual_amount');
            $groups[$group] = $row($b, $a);
            $bMat += $b;
            $aMat += $a;
        }

        $bCm = (float) $this->budget_cm_cost * 12;
        $aCm = (float) $this->actual_cm_cost * 12;
        $bCom = (float) $this->budget_commercial_cost * 12;
        $aCom = ($aMat + $aCm) * (float) $this->actual_commercial_percent / 100;
        $bOther = (float) $this->budget_other_cost * 12;
        $aOther = (float) $this->actual_other_cost * 12;
        $bCost = $bMat + $bCm + $bCom + $bOther;
        $aCost = $aMat + $aCm + $aCom + $aOther;

        $orderQty = (int) $this->order_qty;
        $actualQty = $this->actualQtyBasis();
        $price = (float) $this->selling_price;
        $bTotal = $bCost / 12 * $orderQty;
        $aTotal = $aCost / 12 * $actualQty;
        $bRevenue = $price * $orderQty;
        $aRevenue = $price * $actualQty;

        return [
            'groups' => $groups,
            'materials' => $row($bMat, $aMat),
            'cm' => $row($bCm, $aCm),
            'commercial' => $row($bCom, $aCom),
            'other' => $row($bOther, $aOther),
            'cost' => $row($bCost, $aCost),
            'order' => [
                'order_qty' => $orderQty,
                'actual_qty' => $actualQty,
                'shipped' => (bool) $this->shipped_qty,
                'selling_price' => $price,
                'budget' => ['total_cost' => $bTotal, 'revenue' => $bRevenue, 'profit' => $bRevenue - $bTotal,
                    'margin' => $bRevenue > 0 ? ($bRevenue - $bTotal) / $bRevenue * 100 : null],
                'actual' => ['total_cost' => $aTotal, 'revenue' => $aRevenue, 'profit' => $aRevenue - $aTotal,
                    'margin' => $aRevenue > 0 ? ($aRevenue - $aTotal) / $aRevenue * 100 : null],
            ],
        ];
    }

    /** Refresh the cached per-piece totals from the lines. */
    public function recompute(): void
    {
        $this->unsetRelation('items');
        $sum = $this->summary();
        $this->budget_total_cost = $sum['cost']['b']['pc'];
        $this->actual_total_cost = $sum['cost']['a']['pc'];
        $this->save();
    }
}
