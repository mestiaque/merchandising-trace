<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_material_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('order_id')->constrained('mer_orders')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('mer_suppliers')->nullOnDelete();
            // fabric, trim, yarn, accessories
            $table->string('material_type', 30);
            $table->string('material_name');
            $table->decimal('qty', 14, 4);
            $table->foreignId('unit_id')->nullable()->constrained('mer_uoms')->nullOnDelete();
            $table->date('booking_date')->nullable();
            $table->date('expected_date')->nullable();
            // booked -> received -> cancelled
            $table->string('status', 20)->default('booked');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_material_bookings');
    }
};
