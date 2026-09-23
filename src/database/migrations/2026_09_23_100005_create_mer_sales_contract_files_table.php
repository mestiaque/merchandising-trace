<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_sales_contract_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_contract_id')->constrained('mer_sales_contracts')->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_sales_contract_files');
    }
};
