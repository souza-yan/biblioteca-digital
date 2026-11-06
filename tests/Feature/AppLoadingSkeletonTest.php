<?php

use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

it('renders element skeletons on the teacher dashboard without the legacy overlay', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertSee('aria-label="Carregando busca de materiais"', false)
        ->assertSee('aria-label="Carregando categorias"', false)
        ->assertSee('aria-label="Carregando materiais publicados recentemente"', false)
        ->assertSee('role="status"', false)
        ->assertSee('aria-busy="true"', false)
        ->assertDontSee('data-app-loading-skeleton', false);
});

it('renders the element skeleton in the teacher library without the legacy overlay', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('painel.library'))
        ->assertSee('aria-label="Carregando biblioteca de materiais"', false)
        ->assertSee('role="status"', false)
        ->assertSee('aria-busy="true"', false)
        ->assertDontSee('data-app-loading-skeleton', false);
});

it('renders element skeletons in the category library without the legacy overlay', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('painel.library.categories'))
        ->assertSee('aria-label="Carregando categorias"', false)
        ->assertSee('aria-busy="true"', false)
        ->assertSee('Não foi possível carregar as categorias.')
        ->assertDontSee('data-app-loading-skeleton', false);
});

it('renders element skeletons in the favorites library without the legacy overlay', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('painel.favorites'))
        ->assertSee('aria-label="Carregando favoritos"', false)
        ->assertSee('aria-busy="true"', false)
        ->assertSee('Não foi possível carregar os favoritos.')
        ->assertDontSee('data-app-loading-skeleton', false);
});

it('renders element skeletons on material detail pages without the legacy overlay', function (string $role, string $routeName) {
    $user = $role === 'teacher'
        ? User::factory()->teacher()->create()
        : User::factory()->admin()->create();
    $material = $role === 'teacher'
        ? Material::factory()->published()->create()
        : Material::factory()->create();

    $this->actingAs($user)
        ->get(route($routeName, $material))
        ->assertSee('aria-label="Carregando detalhes do material"', false)
        ->assertSee('aria-busy="true"', false)
        ->assertSee('Não foi possível carregar os detalhes do material.')
        ->assertDontSee('data-app-loading-skeleton', false);
})->with([
    'teacher detail' => ['teacher', 'painel.library.show'],
    'management detail' => ['admin', 'painel.materials.show'],
]);

it('renders the management list skeleton on administrative listings', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('painel.materials'))
        ->assertSee('data-skeleton-variant="material-management"', false)
        ->assertSee('data-skeleton-layout="material-management"', false);
});

it('selects the skeleton layout that matches each administrative page', function (string $routeName, string $variant) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route($routeName))
        ->assertSee('data-skeleton-variant="'.$variant.'"', false)
        ->assertSee('data-skeleton-layout="'.$variant.'"', false);
})->with([
    'categories' => ['painel.categories', 'category-management'],
    'users' => ['painel.users', 'user-management'],
    'schools' => ['painel.schools', 'school-management'],
    'activities' => ['painel.activities', 'activity-management'],
    'most downloaded' => ['painel.materials.most-downloaded', 'most-downloaded'],
]);

it('renders profile element skeletons without the legacy overlay', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('profile.show'))
        ->assertSee('aria-label="Carregando dados do perfil"', false)
        ->assertSee('aria-label="Carregando segurança da conta"', false)
        ->assertSee('aria-label="Carregando sessões do navegador"', false)
        ->assertDontSee('data-app-loading-skeleton', false);
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
