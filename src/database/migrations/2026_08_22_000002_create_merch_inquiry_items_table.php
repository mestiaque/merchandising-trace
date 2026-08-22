<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_inquiry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained('mer_inquiries')->cascadeOnDelete();
            $table->string('style_ref')->nullable();
            $table->foreignId('product_type_id')->nullable()->constrained('mer_product_types')->nullOnDelete();
            $table->string('color_ref')->nullable();
            $table->unsignedInteger('qty')->nullable();
            $table->decimal('target_price', 12, 4)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_inquiry_items');
    }
};
