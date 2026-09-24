<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tech pack "Confirm CM" — the CM (per dozen) agreed with the buyer. The
 * cost sheet picks it up as its CM so it's never typed twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->decimal('confirm_cm', 12, 4)->nullable()->after('target_cm');
        });
    }

    public function down(): void
    {
        Schema::table('mer_styles', function (Blueprint $table) {
            $table->dropColumn('confirm_cm');
        });
    }
};
