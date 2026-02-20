<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_nights', function (Blueprint $table) {
            $table->string('brand_logo_path')->nullable()->after('background_image_path');
        });

        Schema::table('ad_banners', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('title');
            $table->string('logo_url')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('ad_banners', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'logo_url']);
        });

        Schema::table('event_nights', function (Blueprint $table) {
            $table->dropColumn('brand_logo_path');
        });
    }
};
