<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->timestamp('pin_verified_at')->nullable()->after('join_token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('pin_verified_at');
        });
    }
};
