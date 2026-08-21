<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_styles', function (Blueprint $table) {
            $table->id();
            $table->string('style_no');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('buyer_id')->constrained('mer_buyers')->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('mer_brands')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('style_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_styles');
    }
};
