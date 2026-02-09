<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_nights', function (Blueprint $table) {
            $table->string('logo_image_path')->nullable()->after('background_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('event_nights', function (Blueprint $table) {
            $table->dropColumn('logo_image_path');
        });
    }
};
