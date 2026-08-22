<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_tna_template_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tna_template_id')->constrained('mer_tna_templates')->cascadeOnDelete();
            $table->string('group_name'); // 'Order status', 'Style Detail', 'Embellishment', 'Sample Status', ...
            $table->string('task_code');  // fit_request, bulk_fabric_lc, thread, cartons, ...
            $table->string('task_name');  // exact Excel column caption
            $table->string('value_type', 10); // date, text, number, status, yesno
            $table->unsignedInteger('sequence')->default(0);
            $table->integer('offset_days')->default(0); // negative = days before anchor
            $table->string('anchor_field', 20)->nullable(); // shipment, pcd, order_confirm, po_due (overrides template default)
            $table->foreignId('responsible_dept_id')->nullable()->constrained('mer_departments')->nullOnDelete();
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('blocks_pcd')->default(false);
            // none, sample, material_booking, production — where auto-fill comes from.
            $table->string('auto_source', 20)->default('none');
            $table->string('auto_source_ref')->nullable(); // e.g. sample_type_code=PP, item_code=ZIPPER
            $table->timestamps();

            $table->unique(['tna_template_id', 'task_code'], 'merch_tna_tpl_task_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_tna_template_tasks');
    }
};
