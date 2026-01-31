<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_nights', function (Blueprint $table) {
            $table->dateTime('starts_at')->nullable()->after('code');
            $table->string('background_image_path')->nullable()->after('ad_banner_id');
            $table->json('overlay_texts')->nullable()->after('background_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('event_nights', function (Blueprint $table) {
            $table->dropColumn(['starts_at', 'background_image_path', 'overlay_texts']);
        });
    }
};
