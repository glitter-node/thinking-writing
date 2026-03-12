<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thought_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_thought_id')->constrained('thoughts')->cascadeOnDelete();
            $table->foreignId('target_thought_id')->constrained('thoughts')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index('source_thought_id');
            $table->index('target_thought_id');
            $table->unique(['source_thought_id', 'target_thought_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thought_links');
    }
};
