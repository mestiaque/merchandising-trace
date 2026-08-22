<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type', 20); // fabric_mill, trims, accessories, wash, print, embroidery, other
            $table->string('country')->nullable();
            $table->string('contact')->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->string('payment_term')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_suppliers');
    }
};
