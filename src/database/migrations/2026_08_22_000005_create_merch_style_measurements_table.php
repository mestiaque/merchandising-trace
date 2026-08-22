<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_style_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('style_id')->constrained('mer_styles')->cascadeOnDelete();
            $table->string('pom_code')->nullable();
            $table->string('pom_name');
            $table->decimal('tolerance_plus', 6, 3)->nullable();
            $table->decimal('tolerance_minus', 6, 3)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('mer_style_measurement_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('style_measurement_id')->constrained('mer_style_measurements')->cascadeOnDelete();
            $table->foreignId('size_id')->constrained('mer_sizes')->cascadeOnDelete();
            $table->decimal('value', 8, 3)->nullable();
            $table->timestamps();

            $table->unique(['style_measurement_id', 'size_id'], 'merch_style_meas_size_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_style_measurement_sizes');
        Schema::dropIfExists('mer_style_measurements');
    }
};
