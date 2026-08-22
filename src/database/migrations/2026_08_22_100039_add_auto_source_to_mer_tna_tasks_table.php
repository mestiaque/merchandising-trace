<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Snapshotted from the template task at generation time — needed so
        // sync services (SampleTnaSyncService, future MaterialBooking sync)
        // can match without joining back through tna_template_task_id.
        Schema::table('mer_tna_tasks', function (Blueprint $table) {
            $table->string('auto_source', 20)->default('none')->after('is_auto');
            $table->string('auto_source_ref')->nullable()->after('auto_source');
        });
    }

    public function down(): void
    {
        Schema::table('mer_tna_tasks', function (Blueprint $table) {
            $table->dropColumn(['auto_source', 'auto_source_ref']);
        });
    }
};
