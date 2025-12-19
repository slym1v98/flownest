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
        Schema::table('posts', function (Blueprint $table) {
            // Update status enum to include pending_review
            $table->dropColumn('status');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending_review', 'published', 'archived'])
                ->default('draft')
                ->after('excerpt');
            
            // Add workflow fields
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('user_id');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_notes')->nullable()->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['reviewed_by', 'reviewed_at', 'review_notes']);
            $table->dropColumn('status');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
        });
    }
};
