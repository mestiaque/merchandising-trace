<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Version control (merchandising.md §4): a style can have multiple
        // BOM versions over time (buyer-requested consumption changes,
        // fabric substitutions, etc). Each row is one immutable version —
        // "New Version" clones the latest into a fresh draft row rather than
        // editing history in place, so (style_id, version) is unique instead
        // of style_id alone.
        Schema::create('mer_boms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('style_id')->constrained('mer_styles')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            // draft -> active -> superseded
            $table->string('status', 20)->default('draft');
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
