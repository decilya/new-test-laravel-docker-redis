<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/**
 * API маршруты (v1).
 *
 * Группируем маршруты и применяем Rate Limiting (60 запросов в минуту).
 */
Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');

    // Валидация UUID на уровне роутера (защита от невалидных строк)
    Route::delete('/users/{id}/avatar', [UserController::class, 'destroyAvatar'])
        ->whereUuid('id')
        ->name('users.avatar.destroy');
});
