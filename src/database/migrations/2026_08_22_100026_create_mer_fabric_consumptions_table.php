<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_fabric_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('style_id')->constrained('mer_styles')->cascadeOnDelete();
            $table->foreignId('color_id')->nullable()->constrained('mer_colors')->nullOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('mer_items')->nullOnDelete();
            $table->decimal('yy', 8, 4); // fabric yards/yield per garment
            $table->decimal('marker_efficiency', 5, 2)->nullable();
            $table->decimal('gsm', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('method', 20)->default('manual'); // marker, manual, cad
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_fabric_consumptions');
    }
};
