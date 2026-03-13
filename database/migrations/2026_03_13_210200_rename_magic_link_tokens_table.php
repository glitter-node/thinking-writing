<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('magic_link_tokens') && ! Schema::hasTable('magic_login_tokens')) {
            Schema::rename('magic_link_tokens', 'magic_login_tokens');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('magic_login_tokens') && ! Schema::hasTable('magic_link_tokens')) {
            Schema::rename('magic_login_tokens', 'magic_link_tokens');
        }
    }
};
