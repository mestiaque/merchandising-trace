<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sampling happens BEFORE (or alongside) order confirmation, so
        // order_id is a soft (unconstrained) reference — the orders table
        // doesn't exist yet at this point in the build and, even once it
        // does, a sample is usually created before the bulk order exists.
        Schema::create('mer_samples', function (Blueprint $table) {
            $table->id();
            $table->string('sample_number')->unique();
            $table->foreignId('buyer_id')->constrained('mer_buyers')->restrictOnDelete();
            $table->foreignId('style_id')->constrained('mer_styles')->restrictOnDelete();
            $table->unsignedBigInteger('order_id')->nullable();
            // proto, fit, pp, size_set, salesman, photoshoot
            $table->string('sample_type', 30);
            $table->unsignedInteger('qty')->default(1);
            $table->foreignId('size_id')->nullable()->constrained('mer_sizes')->nullOnDelete();
            $table->date('request_date')->nullable();
            $table->date('submission_date')->nullable();
            $table->date('approval_date')->nullable();
            // pending -> in_progress -> sent -> approved/rejected/revise
            $table->string('status', 20)->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_samples');
    }
};
