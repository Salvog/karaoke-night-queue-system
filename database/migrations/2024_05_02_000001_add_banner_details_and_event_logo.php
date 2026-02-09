<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ad_banners', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('title');
            $table->string('logo_url')->nullable()->after('image_url');
        });

        Schema::table('event_nights', function (Blueprint $table) {
            $table->string('event_logo_path')->nullable()->after('background_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('ad_banners', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'logo_url']);
        });

        Schema::table('event_nights', function (Blueprint $table) {
            $table->dropColumn('event_logo_path');
        });
    }
};
