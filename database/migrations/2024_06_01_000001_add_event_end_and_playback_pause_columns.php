<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_nights', function (Blueprint $table) {
            $table->dateTime('ends_at')->nullable()->after('starts_at');
        });

        Schema::table('playback_states', function (Blueprint $table) {
            $table->dateTime('paused_at')->nullable()->after('expected_end_at');
        });
    }

    public function down(): void
    {
        Schema::table('event_nights', function (Blueprint $table) {
            $table->dropColumn('ends_at');
        });

        Schema::table('playback_states', function (Blueprint $table) {
            $table->dropColumn('paused_at');
        });
    }
};
