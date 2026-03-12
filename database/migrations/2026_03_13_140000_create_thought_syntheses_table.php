<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thought_syntheses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('synthesized_thought_id')->constrained('thoughts')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index('user_id');
            $table->index('synthesized_thought_id');
        });

        Schema::create('thought_synthesis_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('synthesis_id')->constrained('thought_syntheses')->cascadeOnDelete();
            $table->foreignId('thought_id')->constrained('thoughts')->cascadeOnDelete();

            $table->index('synthesis_id');
            $table->index('thought_id');
            $table->unique(['synthesis_id', 'thought_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thought_synthesis_items');
        Schema::dropIfExists('thought_syntheses');
    }
};
