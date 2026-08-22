<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('mer_item_categories')->nullOnDelete();
            $table->string('type', 20); // fabric, trim, accessory, packing
            $table->foreignId('uom_id')->nullable()->constrained('mer_uoms')->nullOnDelete();
            $table->foreignId('default_supplier_id')->nullable()->constrained('mer_suppliers')->nullOnDelete();
            $table->decimal('default_price', 12, 4)->nullable();
            $table->string('consumption_uom', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_items');
    }
};
