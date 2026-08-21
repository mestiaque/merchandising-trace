<?php

namespace ME\MerchandisingTrace\Observers;

use ME\MerchandisingTrace\Models\ShipmentPlan;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class ShipmentPlanObserver
{
    public function __construct(private readonly DocumentNumberService $documentNumbers)
    {
    }

    public function creating(ShipmentPlan $plan): void
    {
        if (empty($plan->plan_number)) {
            $plan->plan_number = $this->documentNumbers->next(
                ShipmentPlan::class,
                'plan_number',
                config('merchandising-trace.document_prefixes.shipment_plan', 'SP')
            );
        }
    }
}
