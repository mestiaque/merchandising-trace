<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_material_booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('mer_material_bookings')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('mer_items')->restrictOnDelete();
            $table->foreignId('color_id')->nullable()->constrained('mer_colors')->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('booked_qty', 12, 4)->default(0);
            $table->foreignId('uom_id')->nullable()->constrained('mer_uoms')->nullOnDelete();
            $table->decimal('rate', 12, 4)->nullable();
            $table->decimal('amount', 15, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_material_booking_items');
    }
};
