<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::resource('watchables', App\Http\Controllers\WatchableController::class)
    ->middleware(['auth', 'verified'])
    ->names([
        'index' => 'index',
        'create' => 'watchables.create',
        'store' => 'watchables.store',
        'show' => 'watchables.show',
        'edit' => 'watchables.edit',
        'update' => 'watchables.update',
        'destroy' => 'watchables.destroy',
    ]);

require __DIR__.'/settings.php';
