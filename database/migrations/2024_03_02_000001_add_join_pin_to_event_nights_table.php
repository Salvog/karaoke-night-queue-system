<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_nights', function (Blueprint $table) {
            $table->string('join_pin', 20)->nullable()->after('request_cooldown_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('event_nights', function (Blueprint $table) {
            $table->dropColumn('join_pin');
        });
    }
};
