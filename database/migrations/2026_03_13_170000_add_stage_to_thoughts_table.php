<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thoughts', function (Blueprint $table): void {
            $table->string('stage', 20)->default('thought')->after('content');
            $table->index('stage');
        });
    }

    public function down(): void
    {
        Schema::table('thoughts', function (Blueprint $table): void {
            $table->dropIndex(['stage']);
            $table->dropColumn('stage');
        });
    }
};
