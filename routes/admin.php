<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AdminAuthController;
use App\Modules\Auth\Controllers\AdminDashboardController;
use App\Modules\Admin\Controllers\AdminEventsController;
use App\Modules\Admin\Controllers\AdminAdBannerController;
use App\Modules\Admin\Controllers\AdminQueueController;
use App\Modules\Admin\Controllers\AdminSongsController;
use App\Modules\Admin\Controllers\AdminThemeController;

Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::middleware(['admin.auth', 'admin.role'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/events', [AdminEventsController::class, 'index'])->name('admin.events.index');
    Route::get('/events/create', [AdminEventsController::class, 'create'])->name('admin.events.create');
    Route::post('/events', [AdminEventsController::class, 'store'])->name('admin.events.store');
    Route::get('/events/{eventNight}/edit', [AdminEventsController::class, 'edit'])->name('admin.events.edit');
    Route::put('/events/{eventNight}', [AdminEventsController::class, 'update'])->name('admin.events.update');
    Route::delete('/events/{eventNight}', [AdminEventsController::class, 'destroy'])->name('admin.events.destroy');

    Route::get('/songs', [AdminSongsController::class, 'index'])->name('admin.songs.index');
    Route::get('/songs/{song}/edit', [AdminSongsController::class, 'edit'])->name('admin.songs.edit');
    Route::put('/songs/{song}', [AdminSongsController::class, 'update'])->name('admin.songs.update');
    Route::delete('/songs/{song}', [AdminSongsController::class, 'destroy'])->name('admin.songs.destroy');

    Route::get('/venues', [\App\Modules\Admin\Controllers\AdminVenuesController::class, 'index'])->name('admin.venues.index');
    Route::get('/venues/create', [\App\Modules\Admin\Controllers\AdminVenuesController::class, 'create'])->name('admin.venues.create');
    Route::post('/venues', [\App\Modules\Admin\Controllers\AdminVenuesController::class, 'store'])->name('admin.venues.store');
    Route::get('/venues/{venue}/edit', [\App\Modules\Admin\Controllers\AdminVenuesController::class, 'edit'])->name('admin.venues.edit');
    Route::put('/venues/{venue}', [\App\Modules\Admin\Controllers\AdminVenuesController::class, 'update'])->name('admin.venues.update');
    Route::delete('/venues/{venue}', [\App\Modules\Admin\Controllers\AdminVenuesController::class, 'destroy'])->name('admin.venues.destroy');

    Route::get('/events/{eventNight}/queue', [AdminQueueController::class, 'show'])->name('admin.queue.show');
    Route::get('/events/{eventNight}/queue/state', [AdminQueueController::class, 'state'])->name('admin.queue.state');
    Route::post('/events/{eventNight}/queue/skip', [AdminQueueController::class, 'skip'])->name('admin.queue.skip');
    Route::post('/events/{eventNight}/queue/cancel', [AdminQueueController::class, 'cancel'])->name('admin.queue.cancel');
    Route::post('/events/{eventNight}/queue/start', [AdminQueueController::class, 'start'])->name('admin.queue.start');
    Route::post('/events/{eventNight}/queue/stop', [AdminQueueController::class, 'stop'])->name('admin.queue.stop');
    Route::post('/events/{eventNight}/queue/next', [AdminQueueController::class, 'next'])->name('admin.queue.next');
    Route::post('/events/{eventNight}/queue/add', [AdminQueueController::class, 'add'])->name('admin.queue.add');

    Route::get('/events/{eventNight}/theme-ads', [AdminThemeController::class, 'show'])->name('admin.theme.show');
    Route::post('/events/{eventNight}/theme-ads', [AdminThemeController::class, 'update'])->name('admin.theme.update');

    Route::post('/events/{eventNight}/ad-banners', [AdminAdBannerController::class, 'store'])->name('admin.ad-banners.store');
    Route::put('/events/{eventNight}/ad-banners/{adBanner}', [AdminAdBannerController::class, 'update'])->name('admin.ad-banners.update');
    Route::delete('/events/{eventNight}/ad-banners/{adBanner}', [AdminAdBannerController::class, 'destroy'])->name('admin.ad-banners.destroy');
});
