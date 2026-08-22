<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §M03 "Parts & Embellishment tab" (mandatory per spec) — merchandising's
 * own record of which parts a style has and what embellishment each one
 * carries. `trc_part_id` is a soft reference to production-trace's own
 * trc_parts master (no DB FK, same pattern as every other cross-package
 * reference in this build). The handover bridge (§M11 step 5) reads these
 * rows to sync requires_embroidery/requires_print into production's own
 * trc_style_parts table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_style_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('style_id')->constrained('mer_styles')->cascadeOnDelete();
            $table->unsignedBigInteger('trc_part_id');
            $table->unsignedInteger('qty_per_garment')->default(1);
            $table->string('embellishment_type', 20)->default('none'); // none, print, embroidery, applique, studs_stones, heat_seal
            $table->string('placement')->nullable();
            $table->string('artwork_file')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->timestamps();
            $table->unique(['style_id', 'trc_part_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_style_parts');
    }
};
