<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thinking_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->unsignedInteger('thought_count')->default(0);

            $table->index(['user_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thinking_sessions');
    }
};
