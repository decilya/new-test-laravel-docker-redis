<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/**
 * API маршруты (v1).
 */
Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::delete('/users/{id}/avatar', [UserController::class, 'destroyAvatar'])
        ->whereUuid('id')
        ->name('users.avatar.destroy');
});
