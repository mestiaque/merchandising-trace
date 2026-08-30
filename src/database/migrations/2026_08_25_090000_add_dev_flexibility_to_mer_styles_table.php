<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->boolean('requires_dev_sample')->default(true)->after('development_status');
            $table->string('fabric_sourced_by', 20)->default('self')->after('requires_dev_sample'); // self, buyer
        });
    }

    public function down(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->dropColumn(['requires_dev_sample', 'fabric_sourced_by']);
        });
    }
};
