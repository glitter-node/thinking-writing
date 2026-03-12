<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('streams', function (Blueprint $table): void {
            $table->index('position');
        });

        Schema::table('thought_reviews', function (Blueprint $table): void {
            $table->index('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('streams', function (Blueprint $table): void {
            $table->dropIndex(['position']);
        });

        Schema::table('thought_reviews', function (Blueprint $table): void {
            $table->dropIndex(['reviewed_at']);
        });
    }
};
