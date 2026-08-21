<?php

namespace ME\MerchandisingTrace\Observers;

use ME\MerchandisingTrace\Models\Costing;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class CostingObserver
{
    public function __construct(private readonly DocumentNumberService $documentNumbers)
    {
    }

    public function creating(Costing $costing): void
    {
        if (empty($costing->costing_number)) {
            $costing->costing_number = $this->documentNumbers->next(
                Costing::class,
                'costing_number',
                config('merchandising-trace.document_prefixes.costing', 'CST')
            );
        }
    }
}
