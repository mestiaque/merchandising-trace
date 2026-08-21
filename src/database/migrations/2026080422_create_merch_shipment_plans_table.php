<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Business/planning-level shipment tracking — distinct from
        // production-sfl's ProdShipment, which is the operational handover
        // record. Cross-referenced to it by shipment_number matching (soft,
        // no FK — Merchandising doesn't depend on that package).
        Schema::create('mer_shipment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_number')->unique();
            $table->foreignId('order_id')->constrained('mer_orders')->cascadeOnDelete();
            $table->date('planned_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->unsignedInteger('planned_qty')->default(0);
            $table->string('destination_port')->nullable();
            $table->string('mode', 20)->default('sea');
            // planned -> shipped -> delivered -> delayed
            $table->string('status', 20)->default('planned');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_shipment_plans');
    }
};
