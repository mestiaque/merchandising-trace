<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_boms', function (Blueprint $table) {
            $table->id();
            $table->string('bom_no')->unique();
            $table->foreignId('style_id')->constrained('mer_styles')->restrictOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 20)->default('draft'); // draft, submitted, approved, revised
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['style_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_boms');
    }
};
