<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_costings', function (Blueprint $table) {
            $table->id();
            $table->string('costing_number')->unique();
            $table->foreignId('order_id')->constrained('mer_orders')->cascadeOnDelete();
            // pre (before order confirmation) or actual (after production)
            $table->string('type', 20)->default('pre');
            $table->decimal('fob_price', 12, 4)->default(0);
            $table->decimal('fabric_cost', 12, 4)->default(0);
            $table->decimal('trim_cost', 12, 4)->default(0);
            $table->decimal('wash_cost', 12, 4)->default(0);
            $table->decimal('embroidery_print_cost', 12, 4)->default(0);
            $table->decimal('overhead_cost', 12, 4)->default(0);
            // draft -> approved
            $table->string('status', 20)->default('draft');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_costings');
    }
};
