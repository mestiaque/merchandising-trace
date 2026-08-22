<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §M11 "the bridge audit record" — a JSON snapshot of the pre-flight
 * checklist as it stood at push time, plus enough to trace which
 * trc_plan_lines row (in the separate production-trace package/DB) a PO
 * landed on. plan_line_id is a soft reference (no FK constraint), same
 * pattern as trc_plan_lines.merch_order_id on the other side.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_production_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_contract_po_id')->constrained('mer_sales_contract_pos')->cascadeOnDelete();
            $table->unsignedBigInteger('plan_line_id')->nullable();
            $table->dateTime('handover_date');
            $table->foreignId('handed_over_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pcd_status', 20);
            $table->text('override_reason')->nullable();
            $table->json('checklist_snapshot');
            $table->string('status', 20)->default('handed_over');
            $table->text('rollback_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_production_handovers');
    }
};
