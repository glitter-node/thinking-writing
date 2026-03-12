<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thought_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thought_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->text('content');
            $table->timestamps();

            $table->unique(['thought_id', 'version']);
            $table->index('thought_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thought_versions');
    }
};
