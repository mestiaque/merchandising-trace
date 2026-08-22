<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\Bridge\TrcShipment;
use ME\MerchandisingTrace\Models\SalesContractPo;

/**
 * §M12 — plan-vs-actual board. "Plan" is the PO's own effective shipment
 * date/qty plus mer_shipment_bookings; "actual" is read straight from the
 * OTHER package's trc_shipments (never duplicated locally).
 */
class ShipmentPlanService
{
    public function forPo(SalesContractPo $po): array
    {
        $actualQty = 0;
        $lastShipDate = null;

        if ($po->production_plan_line_id) {
            $shipments = TrcShipment::query()->where('plan_line_id', $po->production_plan_line_id)->get();
            $actualQty = (int) $shipments->sum('total_qty');
            $lastShipDate = $shipments->max('shipment_date');
        }

        $plannedQty = $po->effectiveQty();
        $shortQty = max(0, $plannedQty - $actualQty);
        $shortPercent = $plannedQty > 0 ? round($shortQty / $plannedQty * 100, 1) : 0.0;

        return [
            'planned_qty' => $plannedQty,
            'planned_ship_date' => $po->effectiveShipment(),
            'actual_qty' => $actualQty,
            'actual_ship_date' => $lastShipDate,
            'short_qty' => $shortQty,
            'short_percent' => $shortPercent,
            'is_short' => $shortQty > 0 && $actualQty > 0,
        ];
    }
}
