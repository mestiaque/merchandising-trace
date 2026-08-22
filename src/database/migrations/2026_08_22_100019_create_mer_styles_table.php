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
            $table->string('style_no')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('buyer_id')->constrained('mer_buyers')->restrictOnDelete();
            $table->foreignId('inquiry_id')->nullable()->constrained('mer_inquiries')->nullOnDelete();
            $table->foreignId('season_id')->nullable()->constrained('mer_seasons')->nullOnDelete();
            $table->foreignId('merchandiser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('wash_type_id')->nullable()->constrained('mer_wash_types')->nullOnDelete();
            $table->foreignId('product_type_id')->nullable()->constrained('mer_product_types')->nullOnDelete();
            $table->decimal('smv', 8, 2)->nullable();
            $table->decimal('cost_smv', 8, 2)->nullable();
            $table->decimal('target_cm', 12, 4)->nullable();
            $table->text('fabric_description')->nullable();
            $table->string('tech_pack_file')->nullable();
            $table->string('artwork_file')->nullable();
            $table->string('size_chart_file')->nullable();
            // new, in_development, sample_stage, approved, in_production, closed
            $table->string('development_status', 20)->default('new');
            $table->boolean('is_repeat')->default(false);
            $table->foreignId('parent_style_id')->nullable()->constrained('mer_styles')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_styles');
    }
};
