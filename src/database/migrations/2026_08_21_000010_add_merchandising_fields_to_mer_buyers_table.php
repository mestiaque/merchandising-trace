<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_buyers', function (Blueprint $table) {
            $table->foreignId('merchandiser_id')->nullable()->after('code')->constrained('users')->nullOnDelete();
            $table->string('region')->nullable()->after('address');
            $table->string('agent_name')->nullable()->after('region');
            $table->string('payment_term')->nullable()->after('agent_name');
            $table->string('delivery_term', 10)->nullable()->after('payment_term'); // FOB, CIF, CMT, DDP
            $table->decimal('default_aql', 5, 2)->nullable()->after('delivery_term');
            // Soft references — the owning tables (tna_templates,
            // document_checklist_templates) don't exist until later phases.
            $table->unsignedBigInteger('tna_template_id')->nullable()->after('default_aql');
            $table->unsignedBigInteger('doc_checklist_template_id')->nullable()->after('tna_template_id');
            $table->string('logo')->nullable()->after('doc_checklist_template_id');
        });
    }

    public function down(): void
    {
        Schema::table('mer_buyers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('merchandiser_id');
            $table->dropColumn(['region', 'agent_name', 'payment_term', 'delivery_term', 'default_aql', 'tna_template_id', 'doc_checklist_template_id', 'logo']);
        });
    }
};
