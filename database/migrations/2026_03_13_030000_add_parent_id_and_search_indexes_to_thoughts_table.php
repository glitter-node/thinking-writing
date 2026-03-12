<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thoughts', function (Blueprint $table): void {
            $table->foreignId('parent_id')->nullable()->after('user_id')->constrained('thoughts')->nullOnDelete();
            $table->index('parent_id');
            $table->index('position');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            Schema::table('thoughts', function (Blueprint $table): void {
                $table->fullText('content');
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            Schema::table('thoughts', function (Blueprint $table): void {
                $table->dropFullText(['content']);
            });
        }

        Schema::table('thoughts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropIndex(['position']);
        });
    }
};
