<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_tna_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('mer_orders')->cascadeOnDelete();
            $table->string('milestone_name');
            $table->date('planned_date');
            $table->date('actual_date')->nullable();
            // pending -> completed (delay is derived: planned_date passed, not completed)
            $table->string('status', 20)->default('pending');
            $table->boolean('is_escalated')->default(false);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_tna_milestones');
    }
};
