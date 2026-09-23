<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->string('sales_contract_file')->nullable()->after('tech_pack_file');
        });
    }

    public function down(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->dropColumn('sales_contract_file');
        });
    }
};
