<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fully superseded by sample_type_id (mer_sample_types) added in
        // 2026_08_22_000007 — this legacy string column was left NOT NULL
        // with no default, blocking every insert once code stopped setting it.
        Schema::table('mer_samples', function (Blueprint $table) {
            $table->dropColumn('sample_type');
        });
    }

    public function down(): void
    {
        Schema::table('mer_samples', function (Blueprint $table) {
            $table->string('sample_type', 30)->nullable()->after('season_id');
        });
    }
};
