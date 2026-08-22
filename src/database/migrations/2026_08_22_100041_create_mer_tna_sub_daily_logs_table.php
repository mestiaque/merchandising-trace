<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_tna_sub_daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tna_sub_plan_id')->constrained('mer_tna_sub_plans')->cascadeOnDelete();
            $table->date('log_date');
            $table->unsignedInteger('sending_qty')->default(0);
            $table->unsignedInteger('receiving_qty')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['tna_sub_plan_id', 'log_date'], 'merch_tna_sub_log_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_tna_sub_daily_logs');
    }
};
