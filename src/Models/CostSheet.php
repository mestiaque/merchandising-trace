<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;

/**
 * The "Open Cost Sheet". Lines are costed per dozen (see CostSheetItem);
 * header money columns (fabric_cost … total_cost, offer_price) are stored
 * per PIECE because reports read them.
 *
 *   Total Amount   = A fabric + B trims + C wash + D stone + E print + F heat seal
 *   CM             = cm_cost (per pc), from SMV × CPM / efficiency or the tech pack's Confirm CM
 *   Sub Total FOB  = Total Amount + CM
 *   Commercial     = Sub Total × commercial_percent
 *   FOB            = Sub Total + Commercial
 *
 * A sheet may belong to a style, to an inquiry (before the style exists),
 * or to neither (free-text style_ref).
 */
class CostSheet extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'mer_cost_sheets';

    public const STATUSES = ['draft', 'submitted', 'approved', 'rejected', 'revised'];
    public const PRICE_TYPES = ['FOB', 'CM', 'CMT', 'CIF', 'DDP', 'FOC'];

    /** Line group => per-piece header column. */
    public const GROUP_COLUMNS = [
        'fabric' => 'fabric_cost',
        'trims' => 'trims_cost',
        'wash' => 'wash_cost',
        'stone' => 'stone_cost',
        'print' => 'print_emb_cost',
        'heat_seal' => 'heat_seal_cost',
    ];

    protected $fillable = [
        'cost_sheet_no', 'style_id', 'inquiry_id', 'style_ref', 'garment_description', 'size_range', 'costing_date',
        'buyer_id', 'version', 'currency_id', 'exchange_rate', 'order_qty',
        'smv', 'cm_minute_rate', 'efficiency_percent', 'fabric_cost', 'trims_cost', 'accessories_cost',
        'print_emb_cost', 'wash_cost', 'stone_cost', 'heat_seal_cost', 'cm_cost', 'commercial_cost', 'commercial_percent',
        'freight_cost', 'testing_cost', 'overhead_cost', 'total_cost', 'profit_percent', 'profit_amount', 'offer_price',
        'buyer_target_price', 'final_price', 'price_type', 'status', 'prepared_by', 'approved_by', 'approved_at', 'remarks',
        'front_image', 'back_image', 'sketch_image',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'costing_date' => 'date',
    ];

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class, 'inquiry_id');
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
        return $this->hasMany(CostSheetItem::class, 'cost_sheet_id')->orderBy('id');
    }

    /** Style number to print: the linked style's, else the free-text ref. */
    public function styleLabel(): string
    {
        return $this->style->style_no ?? $this->style_ref ?? '-';
    }

    /**
     * CM per piece from SMV: (SMV / efficiency) × cost-per-minute.
     */
    public function calcCm(): float
    {
        if (! $this->smv || ! $this->cm_minute_rate || ! $this->efficiency_percent) {
            return 0.0;
        }

        return ((float) $this->smv / ((float) $this->efficiency_percent / 100)) * (float) $this->cm_minute_rate;
    }

    /** CM per piece actually used: the entered CM, else the SMV formula. */
    public function cmPerPiece(): float
    {
        return (float) $this->cm_cost ?: $this->calcCm();
    }

    /**
     * Per-piece extras from before the Open Cost Sheet layout (flat
     * accessories / freight / testing / overhead). Always 0 on sheets made
     * with the new form; kept so older sheets don't silently change price.
     */
    public function otherCost(): float
    {
        return (float) $this->accessories_cost + (float) $this->freight_cost
            + (float) $this->testing_cost + (float) $this->overhead_cost;
    }

    /** Commercial per piece: commercial_percent of the sub total, else a legacy flat amount. */
    public function commercialFor(float $subTotalPerPiece): float
    {
        return (float) $this->commercial_percent > 0
            ? $subTotalPerPiece * (float) $this->commercial_percent / 100
            : (float) $this->commercial_cost;
    }

    /** Sub total per piece: sections A–F (stored per piece) + CM. */
    private function subTotalPerPiece(): float
    {
        $sections = 0.0;
        foreach (self::GROUP_COLUMNS as $column) {
            $sections += (float) $this->{$column};
        }

        return $sections + $this->cmPerPiece();
    }

    /** Total cost per piece = sub total + commercial (+ legacy extras). */
    public function calcTotalCost(): float
    {
        $subTotal = $this->subTotalPerPiece();

        return $subTotal + $this->commercialFor($subTotal) + $this->otherCost();
    }

    /** FOB per piece. New sheets carry no profit %, so this equals the total cost. */
    public function calcOfferPrice(): float
    {
        $profitFraction = (float) $this->profit_percent / 100;

        return $profitFraction < 1 ? $this->calcTotalCost() / (1 - $profitFraction) : 0.0;
    }

    public function calcMarginPercent(): float
    {
        $final = (float) ($this->final_price ?: $this->offer_price);
        if (! $final) {
            return 0.0;
        }

        return (($final - $this->calcTotalCost()) / $final) * 100;
    }

    /**
     * Every figure printed on the sheet, computed from the lines. Money is
     * per dozen ("dz") and per piece ("pc"); "pct" is a share of FOB / dozen.
     */
    public function summary(): array
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        $groups = [];
        foreach (array_keys(CostSheetItem::GROUPS) as $group) {
            $groups[$group] = (float) $items->where('group', $group)->sum('amount');
        }

        $materialsDz = array_sum($groups);
        $cmDz = $this->cmPerPiece() * 12;
        $subTotalDz = $materialsDz + $cmDz;
        $commercialDz = $this->commercialFor($subTotalDz / 12) * 12;
        $otherDz = $this->otherCost() * 12;
        $costDz = $subTotalDz + $commercialDz + $otherDz;
        $profitFraction = (float) $this->profit_percent / 100;
        $fobDz = $profitFraction > 0 && $profitFraction < 1 ? $costDz / (1 - $profitFraction) : $costDz;
        $pct = fn (float $dz) => $fobDz > 0 ? $dz / $fobDz * 100 : 0.0;

        // Bottom strip: main fabric = first fabric line; fusing / pocketing
        // lines are split out by name, as on the printed sheet.
        $fabricLines = $items->where('group', 'fabric')->values();
        $main = $fabricLines->first();
        $pick = fn (string $pattern) => (float) $fabricLines->filter(fn ($l) => preg_match($pattern, $l->label()))->sum('amount');
        $fusingDz = $pick('/fus|interlin/i');
        $pocketDz = $pick('/pocket|pkt/i');

        return [
            'groups' => collect($groups)->map(fn ($dz) => ['dz' => $dz, 'pc' => $dz / 12, 'pct' => $pct($dz)])->all(),
            'materials' => ['dz' => $materialsDz, 'pc' => $materialsDz / 12],
            'cm' => ['dz' => $cmDz, 'pc' => $cmDz / 12, 'pct' => $pct($cmDz)],
            'sub_total' => ['dz' => $subTotalDz, 'pc' => $subTotalDz / 12],
            'commercial' => [
                'dz' => $commercialDz, 'pc' => $commercialDz / 12,
                'rate' => (float) $this->commercial_percent > 0
                    ? (float) $this->commercial_percent
                    : ($subTotalDz > 0 ? $commercialDz / $subTotalDz * 100 : 0.0),
            ],
            'other' => ['dz' => $otherDz, 'pc' => $otherDz / 12],
            'profit' => ['dz' => $fobDz - $costDz, 'pc' => ($fobDz - $costDz) / 12, 'rate' => (float) $this->profit_percent],
            'fob' => ['dz' => $fobDz, 'pc' => $fobDz / 12],
            'b2b_pct' => $pct($materialsDz),
            'strip' => [
                'fabric_price' => $main ? (float) $main->rate : null,
                'fabric_consumption_pc' => $main ? (float) $main->consumption / 12 : null,
                'fabric_uom' => $main?->uom?->code ?? $main?->uom?->name,
                'fabric_cost_pc' => ($groups['fabric'] - $fusingDz - $pocketDz) / 12,
                'pocket_pc' => $pocketDz / 12,
                'fusing_pc' => $fusingDz / 12,
            ],
        ];
    }

    /**
     * Persists the per-piece header columns from the lines — call after any
     * line add/remove/edit. CM falls back to the SMV formula when not set.
     */
    public function recompute(): void
    {
        $sums = $this->items()->reorder()->selectRaw('`group`, SUM(amount) as total')->groupBy('group')->pluck('total', 'group');

        foreach (self::GROUP_COLUMNS as $group => $column) {
            $this->{$column} = (float) ($sums[$group] ?? 0) / 12;
        }
        // Accessory lines now live in section B (trims); the flat column would double-count them.
        $this->accessories_cost = 0;

        if (! (float) $this->cm_cost) {
            $this->cm_cost = $this->calcCm();
        }

        $this->commercial_cost = $this->commercialFor($this->subTotalPerPiece());
        $this->total_cost = $this->calcTotalCost();
        $this->profit_amount = $this->total_cost * ((float) $this->profit_percent / 100);
        $this->offer_price = $this->calcOfferPrice();
        $this->save();
    }
}
