<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title');
            $table->string('status', 20)->default('todo');
            $table->string('priority', 20)->default('medium');
            $table->timestamp('created_at')->nullable();

            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
