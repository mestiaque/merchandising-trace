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
            // fabric, thread, button, label, hang_tag, poly, carton, accessories, other
            $table->string('item_type', 30);
            // Free-text material description — the approved-vendor raw
            // material catalog lives in the Production package (Material
            // Planning §3), which Merchandising does not depend on, so BOM
            // lines here describe materials by name rather than by FK.
            $table->string('material_name');
            $table->foreignId('unit_id')->nullable()->constrained('mer_uoms')->nullOnDelete();
            // Consumption per single finished garment.
            $table->decimal('consumption', 12, 4);
            $table->decimal('waste_percent', 5, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_bom_items');
    }
};
