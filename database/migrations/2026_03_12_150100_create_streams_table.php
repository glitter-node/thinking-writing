<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();

            $table->index(['space_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streams');
    }
};
