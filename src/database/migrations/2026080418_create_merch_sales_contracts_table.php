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
            $table->string('contract_number')->unique();
            $table->foreignId('order_id')->constrained('mer_orders')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('mer_buyers')->restrictOnDelete();
            $table->date('contract_date')->nullable();
            $table->text('terms')->nullable();
            // draft -> signed -> cancelled
            $table->string('status', 20)->default('draft');
            $table->text('remarks')->nullable();
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
