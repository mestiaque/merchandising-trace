<?php

namespace ME\MerchandisingTrace\Observers;

use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class OrderObserver
{
    public function __construct(private readonly DocumentNumberService $documentNumbers)
    {
    }

    public function creating(Order $order): void
    {
        if (empty($order->po_number)) {
            $order->po_number = $this->documentNumbers->next(
                Order::class,
                'po_number',
                config('merchandising-trace.document_prefixes.order', 'PO')
            );
        }
    }
}
