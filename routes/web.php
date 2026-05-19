<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    
    // Watchlist index and reorder
    Route::get('/watchlist', [App\Http\Controllers\WatchlistController::class, 'index'])
        ->name('watchlist.index');
    Route::get('/index', [App\Http\Controllers\WatchlistController::class, 'index'])
        ->name('index');
    Route::patch('/watchlist/reorder', [App\Http\Controllers\WatchlistController::class, 'reorder'])
        ->name('watchlist.reorder');
    
    // Movies resource
    Route::resource('movies', App\Http\Controllers\MovieController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy']);
    
    // Series resource
    Route::resource('series', App\Http\Controllers\SeriesController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy']);
});

require __DIR__.'/settings.php';
