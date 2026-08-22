<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_tna_sub_plans', function (Blueprint $table) {
            $table->id();
            $table->string('sub_no')->unique();
            $table->foreignId('sales_contract_po_id')->constrained('mer_sales_contract_pos')->cascadeOnDelete();
            $table->string('process_type', 15); // embroidery, print, after_wash
            $table->string('emb_print_type')->nullable();
            $table->date('required_psd')->nullable(); // Process Start Date
            $table->date('required_pfd')->nullable(); // Process Finish Date
            $table->unsignedInteger('required_qty_per_day')->nullable();
            $table->string('plant_name')->nullable();
            $table->foreignId('vendor_id')->nullable()->constrained('mer_suppliers')->nullOnDelete();
            $table->unsignedInteger('po_qty')->default(0);
            $table->string('status', 15)->default('open'); // open, running, completed, closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_tna_sub_plans');
    }
};
