<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('shows only active categories to teachers', function () {
    Category::factory()->create([
        'name' => 'Categoria ativa',
        'slug' => 'categoria-ativa',
    ]);
    Category::factory()->inactive()->create([
        'name' => 'Categoria inativa',
        'slug' => 'categoria-inativa',
    ]);

    $this->actingAs(User::factory()->teacher()->create())
        ->getJson('/categories')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'categoria-ativa')
        ->assertJsonMissing(['slug' => 'categoria-inativa']);
});

it('allows staff to create a category with a generated slug', function () {
    $this->actingAs(User::factory()->staff()->create())
        ->postJson('/categories', [
            'name' => 'Matemática Básica',
            'description' => 'Conteúdos introdutórios.',
        ])
        ->assertCreated()
        ->assertJsonPath('slug', 'matematica-basica')
        ->assertJsonPath('is_active', true);

    $this->assertDatabaseHas('categories', [
        'name' => 'Matemática Básica',
        'slug' => 'matematica-basica',
        'is_active' => true,
    ]);
});

it('returns 403 when a teacher tries to create a category', function () {
    $this->actingAs(User::factory()->teacher()->create())
        ->postJson('/categories', ['name' => 'História'])
        ->assertForbidden();
});

it('returns 422 when a category slug is duplicated', function () {
    Category::factory()->create(['slug' => 'slug-em-uso']);

    $this->actingAs(User::factory()->staff()->create())
        ->postJson('/categories', [
            'name' => 'Outra categoria',
            'slug' => 'slug-em-uso',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('slug')
        ->assertJsonPath('errors.slug.0', 'O slug informado já está em uso.');
});

it('allows staff to update and toggle a category', function () {
    $category = Category::factory()->create([
        'name' => 'Ciências',
        'slug' => 'ciencias',
    ]);
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->putJson("/categories/{$category->id}", ['name' => 'Ciências Naturais'])
        ->assertOk()
        ->assertJsonPath('slug', 'ciencias-naturais');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Ciências Naturais',
        'slug' => 'ciencias-naturais',
    ]);

    $this->actingAs($staff)
        ->patchJson("/categories/{$category->id}/toggle-active")
        ->assertOk()
        ->assertJsonPath('is_active', false);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'is_active' => false,
    ]);
});

it('returns 403 when a teacher tries to update a category', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->teacher()->create())
        ->putJson("/categories/{$category->id}", ['name' => 'Alterada'])
        ->assertForbidden();
});

it('returns 403 when a teacher tries to toggle a category', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->teacher()->create())
        ->patchJson("/categories/{$category->id}/toggle-active")
        ->assertForbidden();
});

it('grants category management only to admins and staff', function (string $role, bool $canManage) {
    $user = match ($role) {
        'admin' => User::factory()->admin()->create(),
        'staff' => User::factory()->staff()->create(),
        default => User::factory()->teacher()->create(),
    };
    $category = Category::factory()->make();

    expect(Gate::forUser($user)->allows('create', Category::class))->toBe($canManage)
        ->and(Gate::forUser($user)->allows('update', $category))->toBe($canManage)
        ->and(Gate::forUser($user)->allows('toggleActive', $category))->toBe($canManage);
})->with([
    'admin' => ['admin', true],
    'staff' => ['staff', true],
    'teacher' => ['teacher', false],
]);
