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
        Schema::create('notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreignUuid('folder_id')->nullable()->constrained('folders', 'id')->nullOnDelete();
            $table->string('title')->default('Untitled');
            $table->text('content')->nullable();
            $table->boolean('is_published')->default(false);
            $table->string('share_token', 64)->unique()->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Index
            $table->index('user_id');
            $table->index('folder_id');
            $table->index('share_token');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
