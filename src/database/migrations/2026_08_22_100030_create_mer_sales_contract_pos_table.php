<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row = one T&A line = one PO + one style + one color.
        Schema::create('mer_sales_contract_pos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_contract_id')->constrained('mer_sales_contracts')->cascadeOnDelete();
            $table->foreignId('style_id')->constrained('mer_styles')->restrictOnDelete();
            $table->foreignId('product_type_id')->nullable()->constrained('mer_product_types')->nullOnDelete();
            $table->foreignId('color_id')->constrained('mer_colors')->restrictOnDelete();
            $table->foreignId('wash_type_id')->nullable()->constrained('mer_wash_types')->nullOnDelete();

            $table->string('po_no');
            $table->date('po_due_date')->nullable();
            $table->unsignedInteger('po_qty')->default(0);
            $table->unsignedInteger('po_qty_revised_1')->nullable();
            $table->unsignedInteger('po_qty_revised_2')->nullable();

            $table->decimal('unit_price', 12, 4)->nullable();
            $table->decimal('total_value', 15, 4)->default(0);
            $table->string('price_type', 10)->nullable();
            $table->decimal('cost_smv', 8, 2)->nullable();
            $table->decimal('cm', 12, 4)->nullable();
            $table->decimal('fob_foc', 12, 4)->nullable();

            $table->date('pcd_date')->nullable();
            $table->date('fty_possible_pcd')->nullable();
            $table->date('pcd_revised_1')->nullable();
            $table->date('pcd_revised_2')->nullable();

            $table->date('shipment_date')->nullable();
            $table->date('fty_committed_delivery')->nullable();
            $table->date('shipment_revised_1')->nullable();
            $table->date('shipment_revised_2')->nullable();

            $table->foreignId('ship_mode_id')->nullable()->constrained('mer_ship_modes')->nullOnDelete();

            // yes, no, na — drive production routing once handed over.
            $table->string('print_emb', 3)->default('na');
            $table->string('emb_applique_ih', 3)->default('na');
            $table->string('studs_stones_ih', 3)->default('na');
            $table->string('heat_seal_ih', 3)->default('na');

            // pending, tna_created, pcd_passed, pcd_failed, in_production, shipped, closed
            $table->string('status', 20)->default('pending');
            // Soft reference — the Production package's plan_lines table
            // isn't owned here; set once the handover bridge (P13) runs.
            $table->unsignedBigInteger('production_plan_line_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['sales_contract_id', 'po_no', 'style_id', 'color_id'], 'merch_sc_po_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_sales_contract_pos');
    }
};
