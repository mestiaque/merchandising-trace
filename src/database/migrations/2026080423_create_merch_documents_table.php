<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->nullable()->constrained('mer_buyers')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('mer_orders')->cascadeOnDelete();
            // buyer_document, tech_pack, po_attachment, artwork, approval_file
            $table->string('document_type', 30);
            $table->string('title');
            $table->string('file_path');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_documents');
    }
};
