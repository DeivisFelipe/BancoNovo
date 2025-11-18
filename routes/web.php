<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => inertia('Guest/Login'));
    Route::get('/register', fn() => inertia('Guest/Register'));
});
