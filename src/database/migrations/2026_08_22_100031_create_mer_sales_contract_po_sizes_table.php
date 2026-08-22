<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_sales_contract_po_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_contract_po_id')->constrained('mer_sales_contract_pos')->cascadeOnDelete();
            $table->foreignId('size_id')->constrained('mer_sizes')->restrictOnDelete();
            $table->unsignedInteger('qty')->default(0);
            $table->timestamps();

            $table->unique(['sales_contract_po_id', 'size_id'], 'merch_sc_po_size_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_sales_contract_po_sizes');
    }
};
