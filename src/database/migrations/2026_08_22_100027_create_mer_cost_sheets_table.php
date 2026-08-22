<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_cost_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('cost_sheet_no')->unique();
            $table->foreignId('style_id')->constrained('mer_styles')->restrictOnDelete();
            $table->foreignId('buyer_id')->constrained('mer_buyers')->restrictOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('currency_id')->nullable()->constrained('mer_currencies')->nullOnDelete();
            $table->decimal('exchange_rate', 12, 4)->default(1);
            $table->unsignedInteger('order_qty')->nullable();
            $table->decimal('smv', 8, 2)->nullable();
            $table->decimal('cm_minute_rate', 12, 4)->nullable();
            $table->decimal('efficiency_percent', 5, 2)->default(100);

            $table->decimal('fabric_cost', 15, 4)->default(0);
            $table->decimal('trims_cost', 15, 4)->default(0);
            $table->decimal('accessories_cost', 15, 4)->default(0);
            $table->decimal('print_emb_cost', 15, 4)->default(0);
            $table->decimal('wash_cost', 15, 4)->default(0);
            $table->decimal('cm_cost', 15, 4)->default(0);
            $table->decimal('commercial_cost', 15, 4)->default(0);
            $table->decimal('freight_cost', 15, 4)->default(0);
            $table->decimal('testing_cost', 15, 4)->default(0);
            $table->decimal('overhead_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 4)->default(0);

            $table->decimal('profit_percent', 6, 2)->default(0);
            $table->decimal('profit_amount', 15, 4)->default(0);
            $table->decimal('offer_price', 15, 4)->nullable();
            $table->decimal('buyer_target_price', 15, 4)->nullable();
            $table->decimal('final_price', 15, 4)->nullable();
            $table->string('price_type', 10)->default('FOB'); // FOB, CM, CMT, CIF, DDP, FOC

            $table->string('status', 20)->default('draft'); // draft, submitted, approved, rejected, revised
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['style_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_cost_sheets');
    }
};
