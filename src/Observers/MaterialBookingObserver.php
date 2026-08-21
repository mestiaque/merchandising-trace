<?php

namespace ME\MerchandisingTrace\Observers;

use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class MaterialBookingObserver
{
    public function __construct(private readonly DocumentNumberService $documentNumbers)
    {
    }

    public function creating(MaterialBooking $booking): void
    {
        if (empty($booking->booking_number)) {
            $booking->booking_number = $this->documentNumbers->next(
                MaterialBooking::class,
                'booking_number',
                config('merchandising-trace.document_prefixes.material_booking', 'MB')
            );
        }
    }
}
