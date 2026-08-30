<?php

namespace ME\MerchandisingTrace\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\TnaPlan;
use ME\MerchandisingTrace\Models\TnaTemplate;

/**
 * §8.2: on contract confirm (or manual "Generate T&A"), creates a TnaPlan +
 * one TnaTask per template task for a PO row, back-calculating
 * plan_date = anchor_date + offset_days (anchor = Shipment / PCD /
 * Order Confirm / PO Due, per task — falling back to the template's
 * default anchor when a task doesn't specify its own).
 *
 * Sample-sourced tasks are pre-marked n/a when the style skips our Dev
 * stage (`requires_dev_sample = false`), and material-booking-sourced
 * tasks are pre-marked n/a when the buyer supplies material directly
 * (`fabric_sourced_by = buyer`) — otherwise those tasks would sit pending
 * forever with nothing left to sync them, permanently blocking PCD.
 */
class TnaPlanGenerationService
{
    public function __construct(private readonly DocumentNumberService $numbers)
    {
    }

    public function generateFor(SalesContractPo $po, ?TnaTemplate $template = null): TnaPlan
    {
        $template ??= TnaTemplate::resolveFor($po->style->buyer_id ?? null, $po->product_type_id)
            ?? TnaTemplate::query()->where('is_default', true)->firstOrFail();

        return DB::transaction(function () use ($po, $template) {
            $plan = TnaPlan::updateOrCreate(
                ['sales_contract_po_id' => $po->id],
                [
                    'tna_no' => $this->numbers->next(TnaPlan::class, 'tna_no', 'TNA'),
                    'tna_template_id' => $template->id,
                    'merchandiser_id' => $po->salesContract->merchandiser_id ?? null,
                    'factory_id' => $po->salesContract->factory_id ?? null,
                    'order_confirmation_due_date' => null,
                    'updated_date' => now(),
                    'pcd_result' => 'pending',
                    'overall_status' => 'on_track',
                ]
            );

            $plan->tasks()->delete();

            $anchors = $this->resolveAnchorDates($po);
            $devSampleRequired = $po->style?->requires_dev_sample ?? true;
            $weSourceMaterial = ($po->style?->fabric_sourced_by ?? 'self') === 'self';

            foreach ($template->tasks as $templateTask) {
                $anchorField = $templateTask->anchor_field ?? $template->anchor;
                $anchorDate = $anchors[$anchorField] ?? null;
                $planDate = $anchorDate ? $anchorDate->copy()->addDays((int) $templateTask->offset_days) : null;

                $status = 'pending';
                if ($templateTask->auto_source === 'sample' && ! $devSampleRequired) {
                    $status = 'na';
                } elseif ($templateTask->auto_source === 'material_booking' && ! $weSourceMaterial) {
                    $status = 'na';
                }

                $plan->tasks()->create([
                    'tna_template_task_id' => $templateTask->id,
                    'group_name' => $templateTask->group_name,
                    'task_code' => $templateTask->task_code,
                    'task_name' => $templateTask->task_name,
                    'value_type' => $templateTask->value_type,
                    'sequence' => $templateTask->sequence,
                    'plan_date' => $planDate,
                    'status' => $status,
                    'is_mandatory' => $templateTask->is_mandatory,
                    'blocks_pcd' => $templateTask->blocks_pcd,
                    'responsible_dept_id' => $templateTask->responsible_dept_id,
                    'is_auto' => $templateTask->auto_source !== 'none',
                    'auto_source' => $templateTask->auto_source,
                    'auto_source_ref' => $templateTask->auto_source_ref,
                ]);
            }

            return $plan;
        });
    }

    /**
     * @return array<string, ?Carbon>
     */
    private function resolveAnchorDates(SalesContractPo $po): array
    {
        return [
            'shipment' => $po->effectiveShipment(),
            'pcd' => $po->effectivePcd(),
            'order_confirm' => $po->salesContract->contract_date ?? null,
            'po_due' => $po->po_due_date,
        ];
    }
}
