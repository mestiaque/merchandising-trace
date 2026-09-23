<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->string('po_no', 100)->nullable()->after('style_no');
        });
    }

    public function down(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->dropColumn('po_no');
        });
    }
};
