<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * sales_contract_file was misplaced on Style — a Sales Contract's own
 * document(s) belong on the Sales Contract entity itself (see
 * mer_sales_contract_files), not on the Style. Dropped here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->dropColumn('sales_contract_file');
        });
    }

    public function down(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->string('sales_contract_file')->nullable()->after('tech_pack_file');
        });
    }
};
