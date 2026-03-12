<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thoughts', function (Blueprint $table): void {
            $table->index(['created_at', 'stream_id'], 'thoughts_created_at_stream_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('thoughts', function (Blueprint $table): void {
            $table->dropIndex('thoughts_created_at_stream_id_index');
        });
    }
};
