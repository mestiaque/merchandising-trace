<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §M11: production-trace's trc_production_plans requires product_id and
 * size_group_id against its OWN master tables (trc_products, trc_size_groups)
 * — separate domains from mer_product_types / mer_sizes with no natural 1:1
 * mapping. Rather than guess, the handover screen asks once per style which
 * trc_products/trc_size_groups row it maps to, cached here so every
 * subsequent handover for that style is fully automatic.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->unsignedBigInteger('trc_product_id')->nullable()->after('product_type_id');
            $table->unsignedBigInteger('trc_size_group_id')->nullable()->after('trc_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->dropColumn(['trc_product_id', 'trc_size_group_id']);
        });
    }
};
