<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playback_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_night_id')->constrained()->cascadeOnDelete()->unique();
            $table->foreignId('current_request_id')->nullable()->constrained('song_requests')->nullOnDelete();
            $table->string('state', 20);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expected_end_at')->nullable();
            $table->timestamps();

            $table->check("state in ('idle','playing','break','paused')");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playback_states');
    }
};
