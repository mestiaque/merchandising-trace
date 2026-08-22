<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_buyers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('merchandiser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('region')->nullable();
            $table->string('agent_name')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('payment_term')->nullable();
            $table->string('delivery_term', 10)->nullable(); // FOB, CIF, CMT, DDP
            $table->decimal('default_aql', 5, 2)->nullable();
            // Soft references — the owning tables (tna_templates,
            // document_checklist_templates) don't exist until later phases.
            $table->unsignedBigInteger('tna_template_id')->nullable();
            $table->unsignedBigInteger('doc_checklist_template_id')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_buyers');
    }
};
