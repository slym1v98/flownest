<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('content_fields', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->foreignUuid('content_type_id')->references('id')->on('content_types')->cascadeOnDelete();
            $table->string('label')->comment('Tên hiển thị (vd: Giá bán)');
            $table->string('key')->comment('Key trong JSON (vd: price)');
            $table->string('type')->comment('Loại dữ liệu: text, number, select, media...');
            $table->json('options')->nullable()->comment('Dùng cho select box, radio...');
            $table->string('rules')->nullable()->comment('Validation rules (required|min:10)');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['content_type_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_fields');
    }
};
