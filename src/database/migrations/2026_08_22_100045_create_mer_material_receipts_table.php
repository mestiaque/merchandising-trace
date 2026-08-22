<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_material_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('mer_material_bookings')->cascadeOnDelete();
            $table->foreignId('consignment_id')->nullable()->constrained('mer_material_consignments')->nullOnDelete();
            $table->date('receive_date');
            $table->foreignId('item_id')->constrained('mer_items')->restrictOnDelete();
            $table->decimal('qty', 12, 4);
            $table->string('store_ref')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_material_receipts');
    }
};
