<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thought_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('thought_id')->constrained()->cascadeOnDelete();
            $table->timestamp('reviewed_at');
            $table->string('review_score', 24);
            $table->timestamps();

            $table->index(['thought_id', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thought_reviews');
    }
};
