<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('mer_boms')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('mer_items')->restrictOnDelete();
            $table->string('item_type', 20); // fabric, trim, accessory, packing (snapshot of mer_items.type)
            $table->foreignId('color_id')->nullable()->constrained('mer_colors')->nullOnDelete();
            $table->foreignId('size_id')->nullable()->constrained('mer_sizes')->nullOnDelete();
            // Garment part this line applies to — free text, since the parts
            // master lives in the downstream Production package, not here.
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

    public function down(): void
    {
        Schema::dropIfExists('mer_bom_items');
    }
};
