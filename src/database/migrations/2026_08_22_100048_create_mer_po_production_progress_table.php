<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §M11 "reverse feed" — a read-model mirror of trc_plan_line_sizes rollups
 * so merchandisers see live production progress without querying
 * production-trace's tables directly from a dashboard.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_po_production_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_contract_po_id')->unique()->constrained('mer_sales_contract_pos')->cascadeOnDelete();
            $table->unsignedInteger('order_qty')->default(0);
            $table->unsignedInteger('cut_qty')->default(0);
            $table->unsignedInteger('sewn_qty')->default(0);
            $table->unsignedInteger('finished_qty')->default(0);
            $table->unsignedInteger('packed_qty')->default(0);
            $table->unsignedInteger('shipped_qty')->default(0);
            $table->unsignedInteger('reject_qty')->default(0);
            $table->decimal('dhu', 8, 2)->default(0);
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_po_production_progress');
    }
};
