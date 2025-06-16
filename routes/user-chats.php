<?php

declare(strict_types=1);
declare(ticks=1000);


use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('chats')
    ->name('chats.')
    ->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::get('/{thread}', [ChatController::class, 'show'])->name('show');
        Route::delete('/{thread}', [ChatController::class, 'deleteThread'])->name('delete-thread');
        Route::delete('/messages/{message}', [ChatController::class, 'deleteMessage'])
            ->name('messages.delete');
        Route::post('/{ad}/messages/{recipient}', [ChatController::class, 'storeMessage'])
            ->name('messages.store')
            ->middleware('throttle:10,1');
    });
