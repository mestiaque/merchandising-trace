<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §M14 — thread-style log per style/PO, searchable across all orders.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_communication_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('style_id')->nullable();
            $table->foreignId('sales_contract_po_id')->nullable()->constrained('mer_sales_contract_pos')->nullOnDelete();
            $table->date('log_date');
            $table->string('direction', 10); // inbound, outbound
            $table->string('channel', 20); // email, whatsapp, call, meeting, other
            $table->string('subject');
            $table->text('body')->nullable();
            $table->string('attachment')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->boolean('follow_up_done')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_communication_logs');
    }
};
