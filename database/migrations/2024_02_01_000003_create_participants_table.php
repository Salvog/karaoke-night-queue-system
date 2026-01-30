<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_night_id')->constrained()->cascadeOnDelete();
            $table->string('device_cookie_id', 64);
            $table->string('join_token_hash', 128);
            $table->string('display_name')->nullable();
            $table->timestamps();

            $table->unique(['event_night_id', 'device_cookie_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
