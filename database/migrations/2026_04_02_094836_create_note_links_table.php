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
        Schema::create('note_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('source_note')->constrained('notes', 'id')->cascadeOnDelete();
            $table->foreignUuid('target_note')->constrained('notes', 'id')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['source_note', 'target_note']);
            
            // Index
            $table->index('source_note');
            $table->index('target_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_links');
    }
};
