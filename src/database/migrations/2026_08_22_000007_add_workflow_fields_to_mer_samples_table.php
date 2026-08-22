<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_samples', function (Blueprint $table) {
            $table->foreignId('sample_type_id')->nullable()->after('sample_type')->constrained('mer_sample_types')->nullOnDelete();
            $table->foreignId('season_id')->nullable()->after('style_id')->constrained('mer_seasons')->nullOnDelete();
            $table->foreignId('merchandiser_id')->nullable()->after('season_id')->constrained('users')->nullOnDelete();
            $table->date('required_date')->nullable()->after('request_date');
            $table->string('size_ref')->nullable()->after('size_id');
            $table->string('color_ref')->nullable()->after('size_ref');
            $table->string('courier_name')->nullable()->after('submission_date');
            $table->string('tracking_no')->nullable()->after('courier_name');
            $table->text('buyer_comments')->nullable()->after('remarks');
            $table->string('attachment')->nullable()->after('buyer_comments');
            $table->unsignedInteger('revision_no')->default(1)->after('attachment');
            $table->foreignId('parent_sample_id')->nullable()->after('revision_no')->constrained('mer_samples')->nullOnDelete();
        });

        // Move existing 'pending'/'sent' rows onto the spec's status vocabulary
        // (requested, in_progress, submitted, approved, rejected, resubmit,
        // cancelled) — safe on this pre-launch dataset (no samples exist yet
        // beyond demo-seeded ones, which use only 'pending'/'approved'/etc.).
        DB::table('mer_samples')->where('status', 'pending')->update(['status' => 'requested']);
        DB::table('mer_samples')->where('status', 'sent')->update(['status' => 'submitted']);
    }

    public function down(): void
    {
        Schema::table('mer_samples', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sample_type_id');
            $table->dropConstrainedForeignId('season_id');
            $table->dropConstrainedForeignId('merchandiser_id');
            $table->dropConstrainedForeignId('parent_sample_id');
            $table->dropColumn([
                'required_date', 'size_ref', 'color_ref', 'courier_name', 'tracking_no',
                'buyer_comments', 'attachment', 'revision_no',
            ]);
        });
    }
};
