<?php

namespace ME\MerchandisingTrace\Services;

use ME\MerchandisingTrace\Models\DocumentTemplate;
use ME\MerchandisingTrace\Models\SalesContract;

/**
 * §M13 — clones the buyer's checklist template (or the global default) into
 * mer_order_documents rows the moment a contract is confirmed.
 */
class DocumentChecklistService
{
    public function generateFor(SalesContract $contract): int
    {
        if ($contract->documents()->exists()) {
            return 0;
        }

        $template = DocumentTemplate::query()->active()->where('buyer_id', $contract->buyer_id)->first()
            ?? DocumentTemplate::query()->active()->where('is_default', true)->first();

        if (! $template) {
            return 0;
        }

        $created = 0;
        foreach ($template->items as $item) {
            $contract->documents()->create([
                'document_template_item_id' => $item->id,
                'name' => $item->name,
                'is_mandatory' => $item->is_mandatory,
                'due_date' => $item->due_offset_days !== null ? $contract->contract_date?->copy()->addDays($item->due_offset_days) : null,
                'status' => 'pending',
            ]);
            $created++;
        }

        return $created;
    }
}
