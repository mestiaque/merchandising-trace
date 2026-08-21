<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Color/size breakdown lines for a buyer order. order_qty on
        // mer_orders is always the sum of qty across these rows.
        Schema::create('mer_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('mer_orders')->cascadeOnDelete();
            $table->foreignId('color_id')->nullable()->constrained('mer_colors')->nullOnDelete();
            $table->foreignId('size_id')->nullable()->constrained('mer_sizes')->nullOnDelete();
            $table->unsignedInteger('qty');
            $table->timestamps();

            $table->unique(['order_id', 'color_id', 'size_id'], 'merch_order_items_unique_line');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_order_items');
    }
};
