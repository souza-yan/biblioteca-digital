<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialVersionController;
use App\Http\Controllers\UserController;
use App\Livewire\Categories\CategoryManager;
use App\Livewire\Materials\MaterialDetail;
use App\Livewire\Materials\MaterialLibrary;
use App\Livewire\Materials\MaterialManager;
use App\Livewire\Users\UserManager;
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

    Route::get('/painel/usuarios', UserManager::class)
        ->middleware('role:admin,staff')
        ->name('painel.users');

    Route::get('/painel/categorias', CategoryManager::class)
        ->middleware('role:admin,staff')
        ->name('painel.categories');

    Route::middleware('role:admin,staff')->group(function () {
        Route::get('/painel/materiais', MaterialManager::class)->name('painel.materials');
        Route::get('/painel/materiais/{material}', MaterialDetail::class)->name('painel.materials.show');
    });

    Route::middleware('role:teacher')->group(function () {
        Route::get('/painel/biblioteca', MaterialLibrary::class)->name('painel.library');
        Route::get('/painel/biblioteca/{material}', MaterialDetail::class)->name('painel.library.show');
    });

    Route::middleware('role:admin,staff')
        ->prefix('users')
        ->name('users.')
        ->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::patch('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('toggle-active');
        });

    Route::prefix('categories')
        ->name('categories.')
        ->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');

            Route::middleware('role:admin,staff')->group(function () {
                Route::post('/', [CategoryController::class, 'store'])->name('store');
                Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
                Route::patch('/{category}/toggle-active', [CategoryController::class, 'toggleActive'])
                    ->name('toggle-active');
            });
        });

    Route::prefix('materials')
        ->name('materials.')
        ->group(function () {
            Route::middleware('role:admin,staff,teacher')->group(function () {
                Route::get('/', [MaterialController::class, 'index'])->name('index');
                Route::get('/{material}', [MaterialController::class, 'show'])->name('show');
            });

            Route::middleware('role:admin,staff')->group(function () {
                Route::post('/', [MaterialController::class, 'store'])->name('store');
                Route::put('/{material}', [MaterialController::class, 'update'])->name('update');
                Route::patch('/{material}/publish', [MaterialController::class, 'publish'])->name('publish');
                Route::patch('/{material}/archive', [MaterialController::class, 'archive'])->name('archive');
            });
        });

    Route::prefix('materials/{material}/versions')
        ->name('materials.versions.')
        ->middleware('role:admin,staff')
        ->group(function () {
            Route::get('/', [MaterialVersionController::class, 'index'])->name('index');
            Route::post('/', [MaterialVersionController::class, 'store'])->name('store');
        });
});
