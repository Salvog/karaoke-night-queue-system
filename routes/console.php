<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('app:welcome', function () {
    $this->info('Karaoke Night Queue System ready.');
})->purpose('Display a welcome message.');
