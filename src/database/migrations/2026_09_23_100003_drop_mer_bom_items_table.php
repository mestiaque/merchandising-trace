<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BOM is now a buyer-provided PDF (mer_boms.bom_file), not a line-item
 * builder — the structured BOM line items this table held are dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('mer_bom_items');
    }

    public function down(): void
    {
        Schema::create('mer_bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('mer_boms')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('mer_items')->restrictOnDelete();
            $table->string('item_type', 20);
            $table->foreignId('color_id')->nullable()->constrained('mer_colors')->nullOnDelete();
            $table->foreignId('size_id')->nullable()->constrained('mer_sizes')->nullOnDelete();
            $table->string('part_name')->nullable();
            $table->decimal('consumption', 12, 4);
            $table->foreignId('uom_id')->nullable()->constrained('mer_uoms')->nullOnDelete();
            $table->decimal('wastage_percent', 5, 2)->default(0);
            $table->decimal('rate', 12, 4)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('mer_currencies')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('mer_suppliers')->nullOnDelete();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }
};
