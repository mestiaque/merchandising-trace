<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained('mer_currencies')->cascadeOnDelete();
            $table->decimal('rate', 12, 4);
            $table->date('effective_date');
            $table->timestamps();

            $table->unique(['currency_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_exchange_rates');
    }
};
