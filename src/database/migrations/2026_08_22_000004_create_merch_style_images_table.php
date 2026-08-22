<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_style_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('style_id')->constrained('mer_styles')->cascadeOnDelete();
            $table->string('path');
            $table->string('type', 20)->default('front'); // front, back, detail, embellishment, artwork
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_style_images');
    }
};
