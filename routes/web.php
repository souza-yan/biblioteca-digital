<?php

use App\Actions\Dashboard\BuildDashboard;
use App\Http\Controllers\DownloadController;
use App\Livewire\Activity\ActivityLogIndex;
use App\Livewire\Categories\CategoryLibrary;
use App\Livewire\Categories\CategoryManager;
use App\Livewire\Materials\FavoriteLibrary;
use App\Livewire\Materials\MaterialDetail;
use App\Livewire\Materials\MaterialLibrary;
use App\Livewire\Materials\MaterialManager;
use App\Livewire\Users\UserManager;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'active',
])->group(function () {

    Route::get('/dashboard', function (BuildDashboard $buildDashboard) {
        $actor = request()->user();
        abort_unless($actor instanceof User, 403);

        return view('dashboard', $buildDashboard->handle($actor));
    })->name('dashboard');

    Route::get('/materiais/{material}/download', [DownloadController::class, 'show'])
        ->name('downloads.show');
    Route::get('/materiais/{material}/versoes/{version}/download', [DownloadController::class, 'version'])
        ->name('downloads.version');

    Route::get('/painel/usuarios', UserManager::class)
        ->middleware('role:admin,staff')
        ->name('painel.users');

    Route::get('/painel/categorias', CategoryManager::class)
        ->middleware('role:admin,staff')
        ->name('painel.categories');

    Route::get('/painel/atividades', ActivityLogIndex::class)
        ->middleware('role:admin,staff')
        ->name('painel.activities');

    Route::middleware('role:admin,staff')->group(function () {
        Route::get('/painel/materiais', MaterialManager::class)->name('painel.materials');
        Route::get('/painel/materiais/{material}', MaterialDetail::class)->name('painel.materials.show');
    });

    Route::middleware('role:teacher')->group(function () {
        Route::get('/painel/biblioteca/categorias', CategoryLibrary::class)->name('painel.library.categories');
        Route::get('/painel/biblioteca', MaterialLibrary::class)->name('painel.library');
        Route::get('/painel/biblioteca/{material}', MaterialDetail::class)->name('painel.library.show');
        Route::get('/painel/favoritos', FavoriteLibrary::class)->name('painel.favorites');
    });

});
