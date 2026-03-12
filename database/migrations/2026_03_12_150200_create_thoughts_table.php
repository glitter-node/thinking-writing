<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thoughts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stream_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->string('priority', 20)->default('medium');
            $table->json('tags')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();

            $table->index(['stream_id', 'position']);
            $table->index(['user_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thoughts');
    }
};
