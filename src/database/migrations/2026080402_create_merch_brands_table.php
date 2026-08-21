<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_brands');
    }
};
