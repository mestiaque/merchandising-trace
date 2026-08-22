<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\MaterialConsignment;
use ME\MerchandisingTrace\Models\MaterialReceipt;
use ME\MerchandisingTrace\Models\TnaTask;

/**
 * §8.4 + §4.9 AC: "On receipt, the linked T&A task (bulk_fabric_1st_
 * consignment, thread, main_label, ...) auto-flips to done with
 * actual_date = receive_date."
 */
class MaterialBookingTnaSyncService
{
    /**
     * Item-based receipts (Sewing/Finishing Trims) — matched by the
     * receipt's item code against auto_source_ref, scoped to the booking's
     * style so only that style's T&A plans are touched.
     */
    public function syncFromReceipt(MaterialReceipt $receipt): int
    {
        $booking = $receipt->booking;
        $itemCode = $receipt->item->code ?? null;

        if (! $itemCode) {
            return 0;
        }

        return $this->completeMatchingTasks($booking, $itemCode, $receipt->receive_date);
    }

    /**
     * Booking header dates (PI/LC/X-mill) — fires whenever those fields are
     * set/changed on a fabric booking.
     */
    public function syncFromBooking(MaterialBooking $booking): int
    {
        if ($booking->type !== 'fabric') {
            return 0;
        }

        $synced = 0;
        $synced += $booking->booking_date ? $this->completeMatchingTasks($booking, 'BOOKING_DATE', $booking->booking_date) : 0;
        $synced += $booking->lc_date ? $this->completeMatchingTasks($booking, 'LC_DATE', $booking->lc_date) : 0;
        $synced += $booking->x_mill_date ? $this->completeMatchingTasks($booking, 'XMILL_DATE', $booking->x_mill_date) : 0;

        return $synced;
    }

    /**
     * A consignment's actual_date fills "Bulk Fabric Nth consignment".
     */
    public function syncFromConsignment(MaterialConsignment $consignment): int
    {
        if (! $consignment->actual_date) {
            return 0;
        }

        $ref = 'CONSIGNMENT_' . $consignment->consignment_no;

        return $this->completeMatchingTasks($consignment->booking, $ref, $consignment->actual_date);
    }

    private function completeMatchingTasks(MaterialBooking $booking, string $ref, \Illuminate\Support\Carbon $date): int
    {
        $tasks = TnaTask::query()
            ->where('auto_source', 'material_booking')
            ->where('auto_source_ref', $ref)
            ->whereHas('plan.salesContractPo', fn ($q) => $q->where('style_id', $booking->style_id))
            ->get();

        foreach ($tasks as $task) {
            $task->update(['actual_date' => $date, 'status' => 'done']);
            $task->plan?->recomputeCompletion();
        }

        return $tasks->count();
    }
}
