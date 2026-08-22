<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_tna_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('buyer_id')->nullable()->constrained('mer_buyers')->nullOnDelete();
            $table->foreignId('product_type_id')->nullable()->constrained('mer_product_types')->nullOnDelete();
            // shipment, pcd, order_confirm — the date every task's offset_days counts from by default.
            $table->string('anchor', 20)->default('shipment');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_tna_templates');
    }
};
