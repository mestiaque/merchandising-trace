<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buyers and suppliers created from now on go through the central Approvals
 * page before they can be picked anywhere (see Models\Concerns\RequiresApproval).
 * The column defaults to 'approved' so every existing row — and seeders —
 * stay usable; the create/import paths set 'pending' explicitly.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['mer_buyers', 'mer_suppliers'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('approval_status', 20)->default('approved')->after('is_active'); // pending, approved, rejected
                $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable()->after('approved_by');
                $table->string('approval_remarks', 1000)->nullable()->after('approved_at');
                $table->index('approval_status');
            });
        }
    }

    public function down(): void
    {
        foreach (['mer_buyers', 'mer_suppliers'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['approval_status']);
                $table->dropConstrainedForeignId('approved_by');
                $table->dropColumn(['approval_status', 'approved_at', 'approval_remarks']);
            });
        }
    }
};
