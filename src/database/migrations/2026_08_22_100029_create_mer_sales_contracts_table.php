<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_sales_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_no')->unique();
            $table->foreignId('buyer_id')->constrained('mer_buyers')->restrictOnDelete();
            $table->foreignId('season_id')->nullable()->constrained('mer_seasons')->nullOnDelete();
            $table->foreignId('merchandiser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('factory_id')->nullable()->constrained('mer_factories')->nullOnDelete();
            $table->foreignId('inquiry_id')->nullable()->constrained('mer_inquiries')->nullOnDelete();
            $table->string('buyer_order_ref')->nullable();
            $table->date('contract_date');
            $table->foreignId('currency_id')->nullable()->constrained('mer_currencies')->nullOnDelete();
            $table->decimal('exchange_rate', 12, 4)->default(1);
            $table->string('delivery_term', 10)->nullable();
            $table->string('payment_term')->nullable();
            $table->string('lc_no')->nullable();
            $table->date('lc_date')->nullable();
            $table->decimal('lc_value', 15, 4)->nullable();
            $table->date('lc_expiry')->nullable();
            $table->unsignedInteger('total_qty')->default(0);
            $table->decimal('total_value', 15, 4)->default(0);
            // draft, confirmed, in_production, shipped, closed, cancelled
            $table->string('status', 20)->default('draft');
            $table->text('remarks')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_sales_contracts');
    }
};
