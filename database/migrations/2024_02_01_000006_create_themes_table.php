<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['venue_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
