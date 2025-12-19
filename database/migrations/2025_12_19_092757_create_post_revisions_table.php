<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->json('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->enum('status', ['draft', 'pending_review', 'published', 'archived']);
            $table->boolean('is_featured')->default(false);
            $table->json('seo_data')->nullable();
            $table->text('reason')->nullable(); // Reason for creating this revision
            $table->timestamps();

            // Index for faster queries
            $table->index(['post_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_revisions');
    }
};
