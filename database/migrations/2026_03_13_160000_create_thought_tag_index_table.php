<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thought_tag_index', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('thought_id')->constrained('thoughts')->cascadeOnDelete();
            $table->string('tag', 120);
            $table->timestamp('created_at')->nullable();

            $table->index('tag');
            $table->index('thought_id');
            $table->unique(['thought_id', 'tag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thought_tag_index');
    }
};
