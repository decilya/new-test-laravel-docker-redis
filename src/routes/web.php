<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('throttle:60,1')->group(function () {
    Route::get('/list', [\App\Http\Controllers\UserController::class, 'list'])->name('api.users.list');

    // Регистрация нового пользователя
    Route::post('/register', [\App\Http\Controllers\UserController::class, 'register'])->name('api.register');
});
