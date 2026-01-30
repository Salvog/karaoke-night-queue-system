<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ad_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('image_url');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['venue_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_banners');
    }
};
