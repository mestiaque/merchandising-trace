<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §M13 — a buyer-specific (or global default when buyer_id is null)
 * document checklist template, cloned into mer_order_documents rows per
 * confirmed sales contract.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_document_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mer_document_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_id')->constrained('mer_document_templates')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('due_offset_days')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->unsignedInteger('sequence')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_document_template_items');
        Schema::dropIfExists('mer_document_templates');
    }
};
