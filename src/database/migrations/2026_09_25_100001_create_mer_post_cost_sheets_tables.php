<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Post Cost Sheet — budget (the approved pre-cost / Open Cost Sheet) vs
 * actual after production/shipment. Lines are costed per DOZEN exactly like
 * the pre-cost (fabric: cons × rate; other sections: cons × rate × 12);
 * header money is per PIECE.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_post_cost_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('post_cost_no')->unique();
            $table->foreignId('cost_sheet_id')->constrained('mer_cost_sheets')->restrictOnDelete();
            $table->foreignId('sales_contract_id')->nullable()->constrained('mer_sales_contracts')->nullOnDelete();
            $table->foreignId('style_id')->nullable()->constrained('mer_styles')->nullOnDelete();
            $table->foreignId('buyer_id')->constrained('mer_buyers')->restrictOnDelete();
            $table->string('style_ref', 150)->nullable();
            $table->string('garment_description')->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('mer_currencies')->nullOnDelete();
            $table->date('costing_date')->nullable();

            $table->unsignedInteger('order_qty')->default(0);    // budget basis (PO qty)
            $table->unsignedInteger('shipped_qty')->default(0);  // actual basis
            $table->decimal('selling_price', 15, 4)->nullable(); // FOB per pc actually sold at

            // Budget side, frozen from the pre-cost at creation (per piece).
            $table->decimal('budget_cm_cost', 15, 4)->default(0);
            $table->decimal('budget_commercial_cost', 15, 4)->default(0);
            $table->decimal('budget_other_cost', 15, 4)->default(0);
            $table->decimal('budget_smv', 8, 2)->nullable();

            // Actual side (per piece, except the commercial %).
            $table->decimal('actual_smv', 8, 2)->nullable();
            $table->decimal('actual_cm_cost', 15, 4)->default(0);
            $table->decimal('actual_commercial_percent', 6, 2)->default(0);
            $table->decimal('actual_other_cost', 15, 4)->default(0);

            // Cached totals for lists/reports (per piece).
            $table->decimal('budget_total_cost', 15, 4)->default(0);
            $table->decimal('actual_total_cost', 15, 4)->default(0);

            $table->string('status', 20)->default('draft'); // draft, approved
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mer_post_cost_sheet_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_cost_sheet_id')->constrained('mer_post_cost_sheets')->cascadeOnDelete();
            $table->string('group', 20); // fabric, trims, wash, stone, print, heat_seal
            $table->foreignId('item_id')->nullable()->constrained('mer_items')->nullOnDelete();
            $table->string('description')->nullable();
            $table->string('supplier_name', 150)->nullable();
            $table->foreignId('uom_id')->nullable()->constrained('mer_uoms')->nullOnDelete();
            $table->decimal('budget_consumption', 14, 4)->default(0);
            $table->decimal('budget_rate', 14, 4)->default(0);
            $table->decimal('budget_amount', 15, 4)->default(0); // per dozen
            $table->decimal('actual_consumption', 14, 4)->default(0);
            $table->decimal('actual_rate', 14, 4)->default(0);
            $table->decimal('actual_amount', 15, 4)->default(0); // per dozen
            $table->string('source', 20)->default('pre_cost'); // pre_cost, booking, manual
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_post_cost_sheet_items');
        Schema::dropIfExists('mer_post_cost_sheets');
    }
};
