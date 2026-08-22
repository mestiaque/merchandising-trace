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
            $table->string('booking_no')->unique();
            $table->string('type', 15); // fabric, trims, accessory, packing
            $table->foreignId('sales_contract_id')->constrained('mer_sales_contracts')->restrictOnDelete();
            $table->foreignId('sales_contract_po_id')->nullable()->constrained('mer_sales_contract_pos')->nullOnDelete();
            $table->foreignId('style_id')->constrained('mer_styles')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('mer_suppliers')->nullOnDelete();
            $table->string('mill_country')->nullable();
            $table->date('booking_date')->nullable(); // Bulk Fabric PI date
            $table->string('pi_no')->nullable();
            $table->date('pi_date')->nullable();
            $table->decimal('pi_value', 15, 4)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('mer_currencies')->nullOnDelete();
            $table->string('lc_no')->nullable();
            $table->date('lc_date')->nullable();
            $table->decimal('lc_value', 15, 4)->nullable();
            $table->string('lc_type', 15)->nullable(); // LC, TT, FOC, Consignment
            $table->date('x_mill_date')->nullable(); // Bulk Fabric X mill
            $table->date('expected_inhouse_date')->nullable();
            // draft, booked, pi_issued, lc_opened, in_transit, partial_received, received, closed, cancelled
            $table->string('status', 20)->default('draft');
            $table->text('remarks')->nullable();
            $table->string('attachment')->nullable();
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
