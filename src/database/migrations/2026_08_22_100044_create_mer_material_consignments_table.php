<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_material_consignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('mer_material_bookings')->cascadeOnDelete();
            $table->unsignedTinyInteger('consignment_no'); // 1..n — 1st, 2nd, 3rd, 4th...
            $table->date('planned_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->decimal('planned_qty', 12, 4)->default(0);
            $table->decimal('received_qty', 12, 4)->default(0);
            $table->string('challan_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('status', 15)->default('pending'); // pending, shipped, received, short
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'consignment_no'], 'merch_material_consignment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_material_consignments');
    }
};
