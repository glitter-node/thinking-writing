<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thought_graph_index', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('thought_id')->constrained('thoughts')->cascadeOnDelete();
            $table->foreignId('linked_thought_id')->constrained('thoughts')->cascadeOnDelete();
            $table->string('link_type', 32);
            $table->unsignedInteger('depth')->default(1);
            $table->timestamp('created_at')->nullable();

            $table->index('thought_id');
            $table->index('linked_thought_id');
            $table->index('depth');
            $table->unique(['thought_id', 'linked_thought_id', 'link_type', 'depth'], 'thought_graph_index_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thought_graph_index');
    }
};
