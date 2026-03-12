<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thought_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thought_id')->constrained()->cascadeOnDelete();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->timestamps();

            $table->unique(['thought_id', 'space_id']);
            $table->index('thought_id');
            $table->index('space_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thought_positions');
    }
};
