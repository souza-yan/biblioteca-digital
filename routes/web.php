<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-ui', function () {
    return view('test-ui');
})->name('test-ui');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'active',
])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware('role:admin,staff')
        ->prefix('users')
        ->name('users.')
        ->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::patch('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('toggle-active');
        });
});
