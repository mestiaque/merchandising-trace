<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** §4.3 "style_operations (optional SMV breakdown)". */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_style_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('style_id')->constrained('mer_styles')->cascadeOnDelete();
            $table->string('operation_name');
            $table->string('machine_type')->nullable();
            $table->decimal('smv', 8, 4)->default(0);
            $table->unsignedInteger('sequence')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_style_operations');
    }
};
