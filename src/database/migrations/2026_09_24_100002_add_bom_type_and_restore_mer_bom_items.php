<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A BOM is now one of two kinds:
 *  - file:   the buyer provided one — we just store their PDF (bom_file);
 *  - manual: we build it line by line (mer_bom_items, restored here after
 *            2026_09_23_100003 dropped it).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_boms', function (Blueprint $table) {
            $table->string('bom_type', 10)->default('file')->after('version');
        });

        if (! Schema::hasTable('mer_bom_items')) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_bom_items');

        Schema::table('mer_boms', function (Blueprint $table) {
            $table->dropColumn('bom_type');
        });
    }
};
