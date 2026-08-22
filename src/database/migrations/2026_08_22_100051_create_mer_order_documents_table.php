<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §M13 AC: "No order can be marked closed while a mandatory document is
 * missing" — enforced against these rows, one per checklist item per
 * confirmed sales contract.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_order_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_contract_id')->constrained('mer_sales_contracts')->cascadeOnDelete();
            $table->unsignedBigInteger('document_template_item_id')->nullable();
            $table->string('name');
            $table->boolean('is_mandatory')->default(true);
            $table->date('due_date')->nullable();
            $table->string('status', 15)->default('pending'); // pending, uploaded, approved, rejected
            $table->string('file_path')->nullable();
            $table->dateTime('uploaded_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_order_documents');
    }
};
