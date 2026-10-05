<?php

use Illuminate\Support\Facades\Route;

it('does not register the retired JSON CRUD routes', function () {
    expect(Route::has('users.index'))->toBeFalse()
        ->and(Route::has('users.store'))->toBeFalse()
        ->and(Route::has('users.update'))->toBeFalse()
        ->and(Route::has('users.toggle-active'))->toBeFalse()
        ->and(Route::has('categories.index'))->toBeFalse()
        ->and(Route::has('categories.store'))->toBeFalse()
        ->and(Route::has('categories.update'))->toBeFalse()
        ->and(Route::has('categories.toggle-active'))->toBeFalse()
        ->and(Route::has('materials.index'))->toBeFalse()
        ->and(Route::has('materials.show'))->toBeFalse()
        ->and(Route::has('materials.store'))->toBeFalse()
        ->and(Route::has('materials.update'))->toBeFalse()
        ->and(Route::has('materials.publish'))->toBeFalse()
        ->and(Route::has('materials.archive'))->toBeFalse()
        ->and(Route::has('materials.versions.index'))->toBeFalse()
        ->and(Route::has('materials.versions.store'))->toBeFalse()
        ->and(Route::has('downloads.show'))->toBeTrue()
        ->and(Route::has('downloads.version'))->toBeTrue();
});
