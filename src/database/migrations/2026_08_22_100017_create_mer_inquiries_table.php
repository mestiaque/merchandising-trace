<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('inquiry_no')->unique();
            $table->date('inquiry_given_date');
            $table->foreignId('buyer_id')->constrained('mer_buyers')->restrictOnDelete();
            $table->foreignId('season_id')->nullable()->constrained('mer_seasons')->nullOnDelete();
            $table->foreignId('merchandiser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('factory_id')->nullable()->constrained('mer_factories')->nullOnDelete();
            $table->date('order_confirmation_due_date')->nullable();
            $table->foreignId('product_type_id')->nullable()->constrained('mer_product_types')->nullOnDelete();
            $table->text('description')->nullable();
            $table->unsignedInteger('target_qty')->nullable();
            $table->decimal('target_price', 12, 4)->nullable();
            $table->date('target_ship_date')->nullable();
            $table->string('status', 20)->default('open'); // open, quoted, confirmed, lost, cancelled
            $table->string('lost_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_inquiries');
    }
};
