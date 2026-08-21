<?php

namespace ME\MerchandisingTrace\Observers;

use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class SampleObserver
{
    public function __construct(private readonly DocumentNumberService $documentNumbers)
    {
    }

    public function creating(Sample $sample): void
    {
        if (empty($sample->sample_number)) {
            $sample->sample_number = $this->documentNumbers->next(
                Sample::class,
                'sample_number',
                config('merchandising-trace.document_prefixes.sample', 'SMP')
            );
        }
    }
}
