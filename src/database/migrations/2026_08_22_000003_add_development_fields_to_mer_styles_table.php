<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->foreignId('inquiry_id')->nullable()->after('style_no')->constrained('mer_inquiries')->nullOnDelete();
            $table->foreignId('season_id')->nullable()->after('inquiry_id')->constrained('mer_seasons')->nullOnDelete();
            $table->foreignId('merchandiser_id')->nullable()->after('season_id')->constrained('users')->nullOnDelete();
            $table->foreignId('wash_type_id')->nullable()->after('merchandiser_id')->constrained('mer_wash_types')->nullOnDelete();
            $table->foreignId('product_type_id')->nullable()->after('wash_type_id')->constrained('mer_product_types')->nullOnDelete();
            $table->decimal('smv', 8, 2)->nullable()->after('product_type_id');
            $table->decimal('cost_smv', 8, 2)->nullable()->after('smv');
            $table->decimal('target_cm', 12, 4)->nullable()->after('cost_smv');
            $table->text('fabric_description')->nullable()->after('target_cm');
            $table->string('tech_pack_file')->nullable()->after('fabric_description');
            $table->string('artwork_file')->nullable()->after('tech_pack_file');
            $table->string('size_chart_file')->nullable()->after('artwork_file');
            // new, in_development, sample_stage, approved, in_production, closed
            $table->string('development_status', 20)->default('new')->after('size_chart_file');
            $table->boolean('is_repeat')->default(false)->after('development_status');
            $table->foreignId('parent_style_id')->nullable()->after('is_repeat')->constrained('mer_styles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inquiry_id');
            $table->dropConstrainedForeignId('season_id');
            $table->dropConstrainedForeignId('merchandiser_id');
            $table->dropConstrainedForeignId('wash_type_id');
            $table->dropConstrainedForeignId('product_type_id');
            $table->dropConstrainedForeignId('parent_style_id');
            $table->dropColumn([
                'smv', 'cost_smv', 'target_cm', 'fabric_description', 'tech_pack_file',
                'artwork_file', 'size_chart_file', 'development_status', 'is_repeat',
            ]);
        });
    }
};
