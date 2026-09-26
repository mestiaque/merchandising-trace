<?php

namespace ME\MerchandisingTrace\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ME\MerchandisingTrace\Models\CostSheet;
use ME\MerchandisingTrace\Models\CostSheetItem;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\MaterialReceipt;
use ME\MerchandisingTrace\Models\PoProductionProgress;
use ME\MerchandisingTrace\Models\PostCostSheet;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\SalesContractPo;

/**
 * Builds Post Cost Sheets and fills their actual side from what the system
 * already knows — nothing is typed twice:
 *  - budget lines, CM, commercial, other = the pre-cost, frozen at creation;
 *  - order qty / selling price           = the style's POs (in the contract, if given);
 *  - shipped qty                         = production progress (shipped_qty);
 *  - actual material cost                = material bookings for the style: received
 *                                          qty (booked qty until anything is received)
 *                                          × booking rate, spread over the garments.
 */
class PostCostService
{
    public function __construct(private DocumentNumberService $numbers)
    {
    }

    public function createFromPreCost(CostSheet $preCost, ?SalesContract $contract, ?int $userId): PostCostSheet
    {
        $preCost->loadMissing('items');

        return DB::transaction(function () use ($preCost, $contract, $userId) {
            $sum = $preCost->summary();
            $order = $this->orderFigures($preCost->style_id, $contract?->id);

            $post = PostCostSheet::create([
                'post_cost_no' => $this->numbers->next(PostCostSheet::class, 'post_cost_no', 'PCS'),
                'cost_sheet_id' => $preCost->id,
                'sales_contract_id' => $contract?->id,
                'style_id' => $preCost->style_id,
                'buyer_id' => $preCost->buyer_id,
                'style_ref' => $preCost->styleLabel(),
                'garment_description' => $preCost->garment_description,
                'currency_id' => $preCost->currency_id,
                'costing_date' => now()->toDateString(),
                'order_qty' => $order['order_qty'] ?: (int) $preCost->order_qty,
                'shipped_qty' => $order['shipped_qty'],
                'selling_price' => $order['selling_price'] ?? ($preCost->final_price ?: $preCost->offer_price),
                'budget_smv' => $preCost->smv,
                'budget_cm_cost' => $sum['cm']['pc'],
                'budget_commercial_cost' => $sum['commercial']['pc'],
                'budget_other_cost' => $sum['other']['pc'],
                // Actual side starts at budget and is corrected from real data / by hand.
                'actual_smv' => $preCost->smv,
                'actual_cm_cost' => $sum['cm']['pc'],
                'actual_commercial_percent' => $sum['commercial']['rate'],
                'actual_other_cost' => $sum['other']['pc'],
                'status' => 'draft',
                'prepared_by' => $userId,
            ]);

            foreach ($preCost->items as $line) {
                $post->items()->create([
                    'group' => $line->group,
                    'item_id' => $line->item_id,
                    'description' => $line->description,
                    'supplier_name' => $line->supplier_name,
                    'uom_id' => $line->uom_id,
                    'budget_consumption' => $line->consumption,
                    'budget_rate' => $line->rate,
                    'actual_consumption' => $line->consumption,
                    'actual_rate' => $line->rate,
                    'source' => 'pre_cost',
                ]);
            }

            $this->refreshActuals($post);

            return $post;
        });
    }

    /**
     * Re-pull shipped qty and booked/received material cost. Lines backed by a
     * booking are overwritten (that is the point of refreshing); lines with no
     * booking behind them keep what the user entered. Returns lines touched.
     */
    public function refreshActuals(PostCostSheet $post): int
    {
        $order = $this->orderFigures($post->style_id, $post->sales_contract_id);
        if ($order['shipped_qty']) {
            $post->shipped_qty = $order['shipped_qty'];
            $post->save();
        }

        $garments = $post->actualQtyBasis();
        $touched = 0;

        if ($garments > 0 && $post->style_id) {
            $post->load('items');
            foreach ($this->bookedMaterials($post->style_id, $post->sales_contract_id) as $itemId => $m) {
                $group = $m['type'] === 'fabric' ? 'fabric' : 'trims';
                $factor = CostSheetItem::factor($group);
                $amountDz = $m['cost'] / $garments * 12;
                $rate = $m['rate'];

                $line = $post->items->firstWhere('item_id', $itemId)
                    ?? $post->items()->make([
                        'group' => $group, 'item_id' => $itemId, 'description' => $m['name'],
                        'supplier_name' => $m['supplier'], 'uom_id' => $m['uom_id'],
                    ]);

                // Keep the exact amount: consumption is derived from it and the booking rate.
                $line->fill([
                    'actual_rate' => $rate,
                    'actual_consumption' => $rate > 0 ? $amountDz / ($rate * CostSheetItem::factor($line->group ?? $group)) : 0,
                    'source' => 'booking',
                ]);
                $line->post_cost_sheet_id = $post->id;
                $line->save();
                $touched++;
            }
        }

        $post->recompute();

        return $touched;
    }

    /** Order qty / shipped qty / qty-weighted selling price of the style's POs. */
    public function orderFigures(?int $styleId, ?int $contractId): array
    {
        if (! $styleId) {
            return ['order_qty' => 0, 'shipped_qty' => 0, 'selling_price' => null];
        }

        $pos = SalesContractPo::query()
            ->where('style_id', $styleId)
            ->when($contractId, fn ($q) => $q->where('sales_contract_id', $contractId))
            ->whereNotIn('status', ['cancelled'])
            ->get();

        $orderQty = (int) $pos->sum(fn ($po) => $po->effectiveQty());
        $value = (float) $pos->sum(fn ($po) => $po->effectiveQty() * (float) $po->unit_price);
        $shipped = (int) PoProductionProgress::query()->whereIn('sales_contract_po_id', $pos->pluck('id'))->sum('shipped_qty');

        return [
            'order_qty' => $orderQty,
            'shipped_qty' => $shipped,
            'selling_price' => $orderQty > 0 && $value > 0 ? $value / $orderQty : null,
        ];
    }

    /**
     * item_id => [cost, rate (qty-weighted), type, name, supplier, uom_id] for
     * every material booked for the style. Received qty counts once anything
     * is received against a booking line's item; booked qty until then.
     */
    public function bookedMaterials(int $styleId, ?int $contractId): Collection
    {
        $bookings = MaterialBooking::query()
            ->with(['items.item', 'supplier'])
            ->where('style_id', $styleId)
            ->when($contractId, fn ($q) => $q->where('sales_contract_id', $contractId))
            ->whereNotIn('status', ['cancelled'])
            ->get();

        $received = MaterialReceipt::query()
            ->whereIn('booking_id', $bookings->pluck('id'))
            ->selectRaw('booking_id, item_id, SUM(qty) as qty')->groupBy('booking_id', 'item_id')
            ->get()->keyBy(fn ($r) => $r->booking_id . ':' . $r->item_id);

        $out = collect();
        foreach ($bookings as $booking) {
            // One booking may hold several lines of the same item (e.g. per color);
            // receipts are per booking + item, so aggregate the lines first.
            foreach ($booking->items->whereNotNull('item_id')->groupBy('item_id') as $itemId => $lines) {
                $bookedQty = (float) $lines->sum('booked_qty');
                $bookedCost = (float) $lines->sum(fn ($l) => (float) $l->booked_qty * (float) $l->rate);
                $rate = $bookedQty > 0 ? $bookedCost / $bookedQty : (float) $lines->first()->rate;
                $receipt = $received[$booking->id . ':' . $itemId] ?? null;
                $qty = $receipt ? (float) $receipt->qty : $bookedQty;
                $first = $lines->first();

                $row = $out->get($itemId, [
                    'cost' => 0.0, 'qty' => 0.0, 'type' => $first->item->type ?? 'trim',
                    'name' => $first->item->name ?? $first->description, 'supplier' => $booking->supplier->name ?? null,
                    'uom_id' => $first->uom_id ?? $first->item?->uom_id,
                ]);
                $row['cost'] += $qty * $rate;
                $row['qty'] += $qty;
                $out->put($itemId, $row);
            }
        }

        return $out->map(fn ($r) => $r + ['rate' => $r['qty'] > 0 ? $r['cost'] / $r['qty'] : 0.0]);
    }
}
