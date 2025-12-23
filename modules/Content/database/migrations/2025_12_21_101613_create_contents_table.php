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
        Schema::create('contents', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->foreignUuid('content_type_id')->references('id')->on('content_types');
            $table->string('title');
            $table->string('slug')->index();
            $table->string('excerpt', 500)->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->json('attributes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
