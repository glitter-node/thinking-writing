<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('thought_id')->constrained('thoughts')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('created_at')->nullable();

            $table->index('thought_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
