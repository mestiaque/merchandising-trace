<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_samples', function (Blueprint $table) {
            $table->id();
            $table->string('sample_no')->unique();
            $table->foreignId('style_id')->constrained('mer_styles')->restrictOnDelete();
            $table->foreignId('buyer_id')->constrained('mer_buyers')->restrictOnDelete();
            $table->foreignId('season_id')->nullable()->constrained('mer_seasons')->nullOnDelete();
            $table->foreignId('sample_type_id')->constrained('mer_sample_types')->restrictOnDelete();
            $table->foreignId('merchandiser_id')->nullable()->constrained('users')->nullOnDelete();
            // Soft reference — an order/sales-contract may not exist yet when
            // the sample is requested (sampling happens before confirmation).
            $table->unsignedBigInteger('order_id')->nullable();
            $table->date('request_date')->nullable();
            $table->date('required_date')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->string('size_ref')->nullable();
            $table->string('color_ref')->nullable();
            $table->date('submit_date')->nullable();
            $table->string('courier_name')->nullable();
            $table->string('tracking_no')->nullable();
            $table->date('approval_date')->nullable();
            // requested, in_progress, submitted, approved, rejected, resubmit, cancelled
            $table->string('status', 20)->default('requested');
            $table->text('remarks')->nullable();
            $table->text('buyer_comments')->nullable();
            $table->string('attachment')->nullable();
            $table->unsignedInteger('revision_no')->default(1);
            $table->foreignId('parent_sample_id')->nullable()->constrained('mer_samples')->nullOnDelete();
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
