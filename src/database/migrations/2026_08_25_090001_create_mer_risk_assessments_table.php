<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('style_id')->constrained('mer_styles')->cascadeOnDelete();
            $table->foreignId('season_id')->nullable()->constrained('mer_seasons')->nullOnDelete();
            $table->string('collection_name')->nullable();
            $table->string('category')->nullable();
            $table->foreignId('sewing_factory_id')->nullable()->constrained('mer_factories')->nullOnDelete();
            $table->foreignId('print_factory_id')->nullable()->constrained('mer_factories')->nullOnDelete();
            $table->foreignId('embroidery_factory_id')->nullable()->constrained('mer_factories')->nullOnDelete();
            $table->foreignId('wash_factory_id')->nullable()->constrained('mer_factories')->nullOnDelete();
            $table->text('design_risk')->nullable();
            $table->text('materials_risk')->nullable();
            $table->text('components_risk')->nullable();
            $table->text('process_risk')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_risk_assessments');
    }
};
