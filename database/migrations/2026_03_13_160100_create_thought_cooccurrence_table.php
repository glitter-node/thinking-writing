<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thought_cooccurrence', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('thought_a_id')->constrained('thoughts')->cascadeOnDelete();
            $table->foreignId('thought_b_id')->constrained('thoughts')->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index('thought_a_id');
            $table->index('thought_b_id');
            $table->unique(['thought_a_id', 'thought_b_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thought_cooccurrence');
    }
};
