<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_tna_task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tna_task_id')->constrained('mer_tna_tasks')->cascadeOnDelete();
            $table->string('field');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->text('reason')->nullable();
        });

        Schema::create('mer_tna_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tna_task_id')->constrained('mer_tna_tasks')->cascadeOnDelete();
            $table->string('alert_type', 20); // due_soon, overdue, blocked_pcd
            $table->date('alert_date');
            $table->foreignId('notified_to')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_tna_alerts');
        Schema::dropIfExists('mer_tna_task_logs');
    }
};
