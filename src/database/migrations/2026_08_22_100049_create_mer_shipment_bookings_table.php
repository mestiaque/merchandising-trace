<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §M12 — forwarder/booking detail + short-shipment reason. Actual
 * shipped_qty is read from the OTHER package's trc_shipments (via the
 * Bridge model) and never duplicated here; this table only holds what
 * merchandising itself owns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_shipment_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_contract_po_id')->constrained('mer_sales_contract_pos')->cascadeOnDelete();
            $table->date('planned_ship_date')->nullable();
            $table->string('forwarder_name')->nullable();
            $table->string('booking_no')->nullable();
            $table->string('vessel_flight')->nullable();
            $table->boolean('is_short')->default(false);
            $table->text('short_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_shipment_bookings');
    }
};
