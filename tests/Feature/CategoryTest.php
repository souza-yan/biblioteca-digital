<?php

use App\Livewire\Categories\CategoryLibrary;
use App\Livewire\Categories\CategoryManager;
use App\Livewire\Materials\MaterialLibrary;
use App\Models\Category;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

it('shows teachers active categories that have published materials only', function () {
    $activeCategory = Category::factory()->create(['name' => 'Categoria ativa']);
    $inactiveCategory = Category::factory()->inactive()->create(['name' => 'Categoria inativa']);
    Material::factory()->published()->for($activeCategory)->create();
    Material::factory()->published()->for($inactiveCategory)->create();

    $this->actingAs(User::factory()->teacher()->create());

    Livewire::test(MaterialLibrary::class)
        ->assertViewHas(
            'categories',
            fn ($categories): bool => $categories->contains('id', $activeCategory->getKey())
                && ! $categories->contains('id', $inactiveCategory->getKey()),
        );
});

it('opens a read-only category library for teachers with links to published materials', function () {
    $teacher = User::factory()->teacher()->create();
    $activeCategory = Category::factory()->create([
        'name' => 'Robótica Criativa',
        'slug' => 'robotica-criativa',
        'description' => 'Projetos e ideias.',
    ]);
    $emptyCategory = Category::factory()->create(['name' => 'Categoria sem materiais']);
    $inactiveCategory = Category::factory()->inactive()->create(['name' => 'Categoria inativa']);
    Material::factory()->published()->for($activeCategory)->create();
    Material::factory()->draft()->for($activeCategory)->create();
    Material::factory()->published()->for($inactiveCategory)->create();
    $this->actingAs($teacher);

    $this->get(route('painel.library.categories'))
        ->assertOk()
        ->assertSee('Robótica Criativa')
        ->assertSee('robotica-criativa')
        ->assertSee('material publicado')
        ->assertSee(route('painel.library', ['categoryFilter' => $activeCategory->getKey()]), false)
        ->assertDontSee('Categoria sem materiais')
        ->assertDontSee('Categoria inativa')
        ->assertDontSee('Nova categoria')
        ->assertDontSee('Editar');

    Livewire::test(CategoryLibrary::class)
        ->assertSee('Robótica Criativa')
        ->assertDontSee('Nova categoria')
        ->assertDontSee('Editar')
        ->assertDontSee('Desativar');
});

it('searches the teacher category library by name, slug, or description', function () {
    $teacher = User::factory()->teacher()->create();
    $match = Category::factory()->create([
        'name' => 'Robótica Criativa',
        'slug' => 'robotica-criativa',
        'description' => 'Projetos de laboratório.',
    ]);
    $other = Category::factory()->create(['name' => 'Matemática']);
    Material::factory()->published()->for($match)->create();
    Material::factory()->published()->for($other)->create();
    $this->actingAs($teacher);

    Livewire::test(CategoryLibrary::class)
        ->set('search', 'laboratório')
        ->assertSee($match->name)
        ->assertDontSee($other->name)
        ->set('search', 'robotica-criativa')
        ->assertSee($match->name)
        ->assertDontSee($other->name);
});

it('forbids staff from the teacher category library route', function () {
    $this->actingAs(User::factory()->staff()->create())
        ->get(route('painel.library.categories'))
        ->assertForbidden();
});

it('allows staff to create a category with a generated slug', function () {
    $this->actingAs(User::factory()->staff()->create());

    Livewire::test(CategoryManager::class)
        ->call('openCreate')
        ->set('form.name', 'Matemática Básica')
        ->set('form.slug', '')
        ->set('form.description', 'Conteúdos introdutórios.')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'Matemática Básica',
        'slug' => 'matematica-basica',
        'is_active' => true,
    ]);
});

it('forbids teachers from category management', function () {
    $this->actingAs(User::factory()->teacher()->create());

    $this->get(route('painel.categories'))->assertForbidden();
    Livewire::test(CategoryManager::class)->assertForbidden();
});

it('rejects duplicate category slugs without persisting a category', function () {
    Category::factory()->create(['slug' => 'slug-em-uso']);
    $this->actingAs(User::factory()->staff()->create());

    Livewire::test(CategoryManager::class)
        ->call('openCreate')
        ->set('form.name', 'Outra categoria')
        ->set('form.slug', 'slug-em-uso')
        ->call('save')
        ->assertHasErrors('form.slug');

    $this->assertDatabaseMissing('categories', ['name' => 'Outra categoria']);
});

it('allows staff to update and deactivate a category', function () {
    $staff = User::factory()->staff()->create();
    $category = Category::factory()->create([
        'name' => 'Ciências',
        'slug' => 'ciencias',
    ]);
    $this->actingAs($staff);

    Livewire::test(CategoryManager::class)
        ->call('editCategory', $category->getKey())
        ->set('form.name', 'Ciências Naturais')
        ->set('form.slug', '')
        ->call('save')
        ->assertHasNoErrors()
        ->call('toggleActive', $category->getKey())
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'id' => $category->getKey(),
        'name' => 'Ciências Naturais',
        'slug' => 'ciencias-naturais',
        'is_active' => false,
    ]);
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
