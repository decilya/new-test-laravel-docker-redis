<?php

use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\UserController;

Route::prefix('api')->middleware('throttle:60,1')->group(function () {
    Route::get('/list', [\App\Http\Controllers\UserController::class, 'list'])->name('api.users.list');

    // Регистрация нового пользователя
    Route::post('/register', [\App\Http\Controllers\UserController::class, 'register'])->name('api.register');
});

// второй метод гитер с использованием представления и пагинации
Route::get('/list-user', [\App\Http\Controllers\UserController::class, 'newList'])->name('users.list');

// Создание нового пользователя (store через обращение к register)
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');

Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}/avatar', [UserController::class, 'removeAvatar'])->name('users.removeAvatar');

Route::get('/',  [\App\Http\Controllers\UserController::class, 'newList']);

