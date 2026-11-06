<?php

use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

it('renders the dashboard skeleton with accessible loading state', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertSee('data-skeleton-variant="dashboard"', false)
        ->assertSee('data-skeleton-layout="dashboard"', false)
        ->assertSee('role="status"', false)
        ->assertSee('x-bind:aria-busy="visible.toString()"', false);
});

it('renders the material grid skeleton in the teacher library', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('painel.library'))
        ->assertSee('data-skeleton-variant="material-grid"', false)
        ->assertSee('data-skeleton-layout="material-grid"', false);
});

it('renders the category grid skeleton in the category library', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('painel.library.categories'))
        ->assertSee('data-skeleton-variant="category-grid"', false)
        ->assertSee('data-skeleton-layout="category-grid"', false);
});

it('renders the material detail skeleton on material pages', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create();

    $this->actingAs($teacher)
        ->get(route('painel.library.show', $material))
        ->assertSee('data-skeleton-variant="material-detail"', false)
        ->assertSee('data-skeleton-layout="material-detail"', false);
});

it('renders the management list skeleton on administrative listings', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('painel.materials'))
        ->assertSee('data-skeleton-variant="management-list"', false)
        ->assertSee('data-skeleton-layout="management-list"', false);
});

it('renders the profile skeleton on the account page', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('profile.show'))
        ->assertSee('data-skeleton-variant="profile"', false)
        ->assertSee('data-skeleton-layout="profile"', false);
});

it('falls back to the generic management skeleton for an unmapped authenticated route', function () {
    Route::middleware('web')->get('/_test/skeleton-fallback', fn (): string => Blade::render('<x-app-loading-skeleton />'))
        ->name('test.skeleton-fallback');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/_test/skeleton-fallback')
        ->assertSee('data-skeleton-variant="fallback"', false)
        ->assertSee('data-skeleton-layout="fallback"', false);
});
