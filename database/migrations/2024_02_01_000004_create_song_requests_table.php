<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('song_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_night_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->unsignedInteger('position')->nullable();
            $table->timestamp('played_at')->nullable();
            $table->timestamps();

            $table->index(['event_night_id', 'status', 'position']);
            $table->index(['participant_id', 'status']);
            $table->check("status in ('queued','playing','played','canceled','skipped')");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_requests');
    }
};
