<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // §6 Rule 2: every qty/PCD/shipment revision requires a reason and
        // is logged — this table is the PO-level log; once the T&A module
        // (P7-P8) exists, per-task revisions log into tna_task_logs instead.
        Schema::create('mer_sales_contract_po_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_contract_po_id')->constrained('mer_sales_contract_pos')->cascadeOnDelete();
            $table->string('field', 30); // po_qty, pcd, shipment
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('reason');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_sales_contract_po_revisions');
    }
};
