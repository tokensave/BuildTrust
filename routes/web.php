<?php

use App\Http\Controllers\DealController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', static function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/deals', [DealController::class, 'index'])->name('user.deals.index');
    Route::post('/deals/{ad}', [DealController::class, 'store'])->name('deals.store');
    Route::post('deals/{deal}/status', [DealController::class, 'updateStatus'])->name('user.deals.updateStatus');
});
Route::get('/dashboard', [MainController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/user-ads.php';
require __DIR__.'/user-chats.php';
