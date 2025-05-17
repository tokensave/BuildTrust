<?php

declare(strict_types=1);
declare(ticks=1000);

use App\Http\Controllers\UserAdsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('user/{user}/ads')
    ->name('user.ads.') // <-- читаемо и работает с Ziggy
    ->group(function () {
        Route::get('/', [UserAdsController::class, 'index'])->name('index');
        Route::get('/create', [UserAdsController::class, 'create'])->name('create');
        Route::post('/', [UserAdsController::class, 'store'])->name('store');
        Route::get('/{ad}', [UserAdsController::class, 'show'])->name('show');
        Route::get('/{ad}/edit', [UserAdsController::class, 'edit'])->name('edit');
        Route::post('/{ad}', [UserAdsController::class, 'update'])->name('update');
        Route::delete('/{ad}', [UserAdsController::class, 'destroy'])->name('destroy');
    });

