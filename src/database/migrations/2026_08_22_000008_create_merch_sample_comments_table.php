<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mer_sample_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('mer_samples')->cascadeOnDelete();
            $table->text('comment');
            $table->foreignId('commented_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('comment_date');
            $table->string('attachment')->nullable();
            $table->boolean('is_buyer_comment')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mer_sample_comments');
    }
};
