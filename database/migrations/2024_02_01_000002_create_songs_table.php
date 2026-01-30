<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist')->nullable();
            $table->unsignedInteger('duration_seconds');
            $table->timestamps();

            $table->index(['artist', 'title']);
            $table->check('duration_seconds > 0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
