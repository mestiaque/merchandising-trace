<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_cost_sheet_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_sheet_id')->constrained('mer_cost_sheets')->cascadeOnDelete();
            $table->string('group', 20); // fabric, trims, accessories, process, commercial
            $table->foreignId('item_id')->nullable()->constrained('mer_items')->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('consumption', 12, 4)->nullable();
            $table->foreignId('uom_id')->nullable()->constrained('mer_uoms')->nullOnDelete();
            $table->decimal('rate', 12, 4)->default(0);
            $table->decimal('amount', 15, 4)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_cost_sheet_items');
    }
};
