<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_tna_plans', function (Blueprint $table) {
            $table->id();
            $table->string('tna_no')->unique();
            $table->foreignId('sales_contract_po_id')->unique()->constrained('mer_sales_contract_pos')->cascadeOnDelete();
            $table->foreignId('tna_template_id')->constrained('mer_tna_templates')->restrictOnDelete();
            $table->date('inquiry_given_date')->nullable();
            $table->foreignId('merchandiser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('order_confirmation_due_date')->nullable();
            $table->foreignId('factory_id')->nullable()->constrained('mer_factories')->nullOnDelete();
            $table->date('updated_date')->nullable();
            $table->string('pcd_result', 10)->default('pending'); // pending, pass, fail
            $table->string('pcd_fail_reason')->nullable();
            $table->foreignId('responsible_dept_id')->nullable()->constrained('mer_departments')->nullOnDelete();
            $table->foreignId('responsible_person_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('overall_status', 15)->default('on_track'); // on_track, at_risk, delayed, completed
            $table->decimal('completion_percent', 5, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_tna_plans');
    }
};
