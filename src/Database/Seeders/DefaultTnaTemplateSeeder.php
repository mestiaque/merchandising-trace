<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use Illuminate\Database\Seeder;
use ME\MerchandisingTrace\Models\Department;
use ME\MerchandisingTrace\Models\TnaTemplate;
use ME\MerchandisingTrace\Models\TnaTemplateTask;

/**
 * §12 Appendix D: seeds the default template that reproduces the uploaded
 * Excel. The "Order status", "Style Detail", "Embellishment" and "PCD Pass
 * or Fail" groups are NOT seeded as tna_template_tasks — they are mirrored/
 * computed fields already living on tna_plans and mer_sales_contract_pos
 * (read-only in the grid), per §8.4/§8.5. Only the independently-tracked
 * task groups (Sample, Wash, Pilot, Fabric, Sewing Trims, Finishing Trims)
 * become real tna_tasks rows.
 */
class DefaultTnaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $template = TnaTemplate::firstOrCreate(
            ['code' => 'DEFAULT'],
            ['name' => 'Default T&A Template', 'anchor' => 'pcd', 'is_default' => true, 'is_active' => true]
        );

        if ($template->tasks()->exists()) {
            return; // already seeded
        }

        $deptId = fn (string $name) => Department::firstOrCreate(['code' => strtoupper(substr($name, 0, 4))], ['name' => $name])->id;

        $rows = [
            // group, code, name, value_type, offset_days, anchor_field, dept, mandatory, blocks_pcd, auto_source, auto_source_ref (mer_sample_types.code)
            ['Sample Status', 'fit_request', 'Fit Request date', 'date', -35, null, 'Sample', true, false, 'sample', 'FIT'],
            ['Sample Status', 'fit_submission', 'Fit Submission', 'date', -32, null, 'Sample', true, false, 'sample', 'FIT'],
            ['Sample Status', 'fit_approval', 'Fit Approval', 'date', -30, null, 'Sample', true, false, 'sample', 'FIT'],
            ['Sample Status', 'fit_2nd_submission', '2nd Fit submission', 'date', -27, null, 'Sample', false, false, 'sample', 'FIT2'],
            ['Sample Status', 'fit_2nd_approval', '2nd Fit Approval', 'date', -25, null, 'Sample', false, false, 'sample', 'FIT2'],
            ['Sample Status', 'pp1_request', '1st PP request', 'date', -23, null, 'Sample', true, false, 'sample', 'PP1'],
            ['Sample Status', 'pp1_submit', '1st PP submit', 'date', -21, null, 'Sample', true, false, 'sample', 'PP1'],
            ['Sample Status', 'pp1_approval', '1st PP Approval', 'date', -20, null, 'Sample', true, true, 'sample', 'PP1'],

            ['Wash Status', 'wash_standard_approval', 'Wash Standard Approval', 'date', -18, null, 'Wash', true, true, 'sample', 'WASHSTD'],
            ['Wash Status', 'shade_band_submission', 'Shade band submission', 'date', -17, null, 'Wash', false, false, 'sample', 'SHADEBAND'],
            ['Wash Status', 'shade_band_approval', 'Shade band Approval', 'date', -16, null, 'Wash', false, false, 'sample', 'SHADEBAND'],

            ['Pilot Status', 'file_handover', 'File Hand over Date', 'date', -14, null, 'Production', true, true, 'none'],
            ['Pilot Status', 'pullout', 'Size set Fabric & Trims (Pullout)', 'date', -12, null, 'Production', true, true, 'none'],
            ['Pilot Status', 'pilot_stitching', 'Pilot Stitching', 'date', -6, null, 'Production', false, false, 'production'],
            ['Pilot Status', 'pilot_wash', 'Pilot Wash', 'date', -5, null, 'Production', false, false, 'production'],
            ['Pilot Status', 'pilot_review', 'Pilot Review', 'date', -3, null, 'Production', false, false, 'production'],
            ['Pilot Status', 'pp_meeting', 'PP Meeting', 'date', -2, null, 'Merchandiser', true, true, 'none'],
            ['Pilot Status', 'pilot_remarks', 'Remarks', 'text', 0, null, 'Production', false, false, 'none'],

            ['Fabric Status', 'fabric_yy', 'Fabric YY', 'number', -30, null, 'Fabric', false, false, 'none'],
            ['Fabric Status', 'fabric_requirement', 'Fabric Requirement', 'number', -30, null, 'Fabric', false, false, 'none'],
            ['Fabric Status', 'bulk_fabric_pi', 'Bulk Fabric PI (Booking date)', 'date', -28, null, 'Fabric', true, false, 'material_booking'],
            ['Fabric Status', 'bulk_fabric_lc', 'Bulk Fabric LC', 'date', -25, null, 'Fabric', false, false, 'material_booking'],
            ['Fabric Status', 'fabric_mill_country', 'Fabric Mill Country', 'text', -25, null, 'Fabric', false, false, 'none'],
            ['Fabric Status', 'bulk_fabric_xmill', 'Bulk Fabric X mill', 'date', -18, null, 'Fabric', false, false, 'material_booking'],
            ['Fabric Status', 'bulk_fabric_consignment_1', 'Bulk Fabric 1st consignment', 'date', -15, null, 'Fabric', true, true, 'material_booking'],
            ['Fabric Status', 'bulk_fabric_consignment_2', 'Bulk Fabric 2nd consignment', 'date', -12, null, 'Fabric', false, false, 'material_booking'],
            ['Fabric Status', 'bulk_fabric_consignment_3', 'Bulk Fabric 3rd consignment', 'date', -9, null, 'Fabric', false, false, 'material_booking'],
            ['Fabric Status', 'bulk_fabric_consignment_4', 'Bulk Fabric 4th consignment', 'date', -6, null, 'Fabric', false, false, 'material_booking'],
            ['Fabric Status', 'fabric_remarks', 'Remarks', 'text', 0, null, 'Fabric', false, false, 'none'],

            ['Sewing trims Status', 'thread', 'Thread', 'date', -10, null, 'Trims', true, true, 'material_booking'],
            ['Sewing trims Status', 'zipper', 'Zipper', 'date', -10, null, 'Trims', true, true, 'material_booking'],
            ['Sewing trims Status', 'main_label', 'Main Label', 'date', -10, null, 'Trims', true, true, 'material_booking'],
            ['Sewing trims Status', 'size_label', 'Size Label', 'date', -10, null, 'Trims', true, true, 'material_booking'],
            ['Sewing trims Status', 'care_label', 'Care Label', 'date', -10, null, 'Trims', true, true, 'material_booking'],
            ['Sewing trims Status', 'elastics', 'Elastics', 'date', -10, null, 'Trims', true, true, 'material_booking'],
            ['Sewing trims Status', 'buttons_sewing', 'Buttons', 'date', -10, null, 'Trims', true, true, 'material_booking'],
            ['Sewing trims Status', 'velcro', 'Velcro', 'date', -10, null, 'Trims', true, true, 'material_booking'],
            ['Sewing trims Status', 'sewing_trims_remarks', 'Remarks', 'text', 0, null, 'Trims', false, false, 'none'],

            ['Finishing Trims', 'price_tag', 'Price tag', 'date', -5, null, 'Trims', false, false, 'material_booking'],
            ['Finishing Trims', 'price_stickers', 'Price Stickers', 'date', -5, null, 'Trims', false, false, 'material_booking'],
            ['Finishing Trims', 'buttons_finishing', 'Buttons', 'date', -5, null, 'Trims', false, false, 'material_booking'],
            ['Finishing Trims', 'cords', 'Cords', 'date', -5, null, 'Trims', false, false, 'material_booking'],
            ['Finishing Trims', 'poly_bags', 'Poly Bags', 'date', -5, null, 'Trims', false, false, 'material_booking'],
            ['Finishing Trims', 'others_finishing', 'Others', 'date', -5, null, 'Trims', false, false, 'material_booking'],
            ['Finishing Trims', 'cartons', 'Cartons', 'date', -5, null, 'Trims', false, false, 'material_booking'],
        ];

        foreach ($rows as $i => $row) {
            [$group, $code, $name, $valueType, $offset, $anchorField, $dept, $mandatory, $blocksPcd, $autoSource] = $row;
            $autoSourceRef = $row[10] ?? null;

            TnaTemplateTask::create([
                'tna_template_id' => $template->id,
                'group_name' => $group,
                'task_code' => $code,
                'task_name' => $name,
                'value_type' => $valueType,
                'sequence' => $i + 1,
                'offset_days' => $offset,
                'anchor_field' => $anchorField,
                'responsible_dept_id' => $deptId($dept),
                'is_mandatory' => $mandatory,
                'blocks_pcd' => $blocksPcd,
                'auto_source' => $autoSource,
                'auto_source_ref' => $autoSourceRef,
            ]);
        }
    }
}
