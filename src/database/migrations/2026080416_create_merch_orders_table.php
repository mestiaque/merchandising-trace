<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('buyer_id')->constrained('mer_buyers')->restrictOnDelete();
            $table->foreignId('style_id')->constrained('mer_styles')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->unsignedInteger('order_qty')->default(0);
            $table->date('delivery_date')->nullable();
            $table->decimal('price', 12, 4)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->string('status', 30)->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_orders');
    }
};
