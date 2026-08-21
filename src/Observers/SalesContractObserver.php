<?php

namespace ME\MerchandisingTrace\Observers;

use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class SalesContractObserver
{
    public function __construct(private readonly DocumentNumberService $documentNumbers)
    {
    }

    public function creating(SalesContract $contract): void
    {
        if (empty($contract->contract_number)) {
            $contract->contract_number = $this->documentNumbers->next(
                SalesContract::class,
                'contract_number',
                config('merchandising-trace.document_prefixes.sales_contract', 'SC')
            );
        }
    }
}
