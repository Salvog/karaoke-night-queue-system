<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ad_banners', function (Blueprint $table) {
            $table->string('subtitle', 160)->nullable()->after('title');
            $table->string('logo_url')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('ad_banners', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'logo_url']);
        });
    }
};
