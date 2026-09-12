<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use Illuminate\Database\Seeder;
use ME\MerchandisingTrace\Models\TnaPlan;
use ME\MerchandisingTrace\Models\TnaTemplate;
use ME\MerchandisingTrace\Models\TnaTemplateTask;

/**
 * Adds the 3 T&A task codes production-trace's Pre-Production Readiness
 * Report needs that DefaultTnaTemplateSeeder never seeded (Pattern /
 * Marker / Testing — see TrcExtendedReportService::preProductionReadiness()).
 * Idempotent via firstOrCreate, safe to run on an already-seeded install.
 *
 * Also backfills the same 3 tasks onto every EXISTING TnaPlan built from
 * this template: TnaPlanGenerationService::generateFor() fully deletes and
 * rebuilds a plan's tasks on every call, which would wipe already-recorded
 * progress on real orders, so this inserts only the missing rows directly
 * instead of ever re-calling generateFor(). Not marked blocks_pcd — this
 * only adds readiness *tracking*, it must not change any order's existing
 * PCD pass/fail outcome.
 */
class AddReadinessTnaTasksSeeder extends Seeder
{
    private const NEW_TASKS = [
        ['group' => 'Pilot Status', 'code' => 'pattern_approval', 'name' => 'Pattern Approval', 'offset' => -13, 'sequence' => 100],
        ['group' => 'Pilot Status', 'code' => 'marker_approval', 'name' => 'Marker Approval', 'offset' => -13, 'sequence' => 101],
        ['group' => 'Sample Status', 'code' => 'lab_test_approval', 'name' => 'Lab Test Approval', 'offset' => -20, 'sequence' => 102],
    ];

    public function run(): void
    {
        $template = TnaTemplate::where('code', 'DEFAULT')->first();
        if (! $template) {
            return;
        }

        $deptId = $template->tasks()->first()?->responsible_dept_id;

        $templateTaskIds = [];
        foreach (self::NEW_TASKS as $t) {
            $templateTaskIds[$t['code']] = TnaTemplateTask::firstOrCreate(
                ['tna_template_id' => $template->id, 'task_code' => $t['code']],
                [
                    'group_name' => $t['group'], 'task_name' => $t['name'], 'value_type' => 'date',
                    'sequence' => $t['sequence'], 'offset_days' => $t['offset'], 'anchor_field' => null,
                    'responsible_dept_id' => $deptId, 'is_mandatory' => true, 'blocks_pcd' => false,
                    'auto_source' => 'none', 'auto_source_ref' => null,
                ]
            )->id;
        }

        TnaPlan::where('tna_template_id', $template->id)->with('tasks')
            ->chunkById(200, function ($plans) use ($templateTaskIds, $deptId) {
                foreach ($plans as $plan) {
                    $existingCodes = $plan->tasks->pluck('task_code')->all();
                    foreach (self::NEW_TASKS as $t) {
                        if (in_array($t['code'], $existingCodes, true)) {
                            continue;
                        }
                        $plan->tasks()->create([
                            'tna_template_task_id' => $templateTaskIds[$t['code']],
                            'group_name' => $t['group'], 'task_code' => $t['code'], 'task_name' => $t['name'],
                            'value_type' => 'date', 'sequence' => $t['sequence'], 'plan_date' => null,
                            'status' => 'pending', 'is_mandatory' => true, 'blocks_pcd' => false,
                            'responsible_dept_id' => $deptId, 'is_auto' => false, 'auto_source' => 'none',
                        ]);
                    }
                }
            });
    }
}
