<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thinking_prompts', function (Blueprint $table): void {
            $table->id();
            $table->text('prompt');
            $table->string('category', 80)->index();
            $table->timestamp('created_at')->nullable();
        });

        DB::table('thinking_prompts')->insert([
            ['prompt' => 'What idea did you have today?', 'category' => 'reflection', 'created_at' => now()],
            ['prompt' => 'What problem are you thinking about?', 'category' => 'reflection', 'created_at' => now()],
            ['prompt' => 'What did you learn today?', 'category' => 'learning', 'created_at' => now()],
            ['prompt' => 'What could be improved in your workflow?', 'category' => 'workflow', 'created_at' => now()],
            ['prompt' => 'What scalability problem are you exploring?', 'category' => 'systems', 'created_at' => now()],
            ['prompt' => 'Where is the current user experience breaking down?', 'category' => 'product', 'created_at' => now()],
            ['prompt' => 'What pattern keeps repeating in your work?', 'category' => 'workflow', 'created_at' => now()],
            ['prompt' => 'What concept deserves a clearer explanation?', 'category' => 'learning', 'created_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('thinking_prompts');
    }
};
