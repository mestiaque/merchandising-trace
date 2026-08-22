<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_tna_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tna_plan_id')->constrained('mer_tna_plans')->cascadeOnDelete();
            $table->foreignId('tna_template_task_id')->nullable()->constrained('mer_tna_template_tasks')->nullOnDelete();
            // Snapshot at creation — a later template edit never rewrites history.
            $table->string('group_name');
            $table->string('task_code');
            $table->string('task_name');
            $table->string('value_type', 10);
            $table->unsignedInteger('sequence')->default(0);

            $table->date('plan_date')->nullable();
            $table->date('revised_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->string('value_text')->nullable();
            $table->decimal('value_number', 14, 4)->nullable();
            $table->string('status', 15)->default('pending'); // pending, in_progress, done, approved, na, delayed
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('blocks_pcd')->default(false);
            $table->foreignId('responsible_dept_id')->nullable()->constrained('mer_departments')->nullOnDelete();
            $table->foreignId('responsible_person_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->string('attachment')->nullable();
            $table->boolean('is_auto')->default(false);
            $table->timestamps();

            $table->unique(['tna_plan_id', 'task_code'], 'merch_tna_task_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_tna_tasks');
    }
};
