<?php

use App\Livewire\Categories\CategoryManager;
use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;

it('allows admins and staff to create categories with generated unique slugs', function (string $role) {
    $user = match ($role) {
        'admin' => User::factory()->admin()->create(),
        'staff' => User::factory()->staff()->create(),
    };

    $this->actingAs($user);

    Livewire::test(CategoryManager::class)
        ->call('openCreate')
        ->set('form.name', 'Matemática Básica')
        ->set('form.slug', '')
        ->set('form.description', 'Conteúdos iniciais')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'Matemática Básica',
        'slug' => 'matematica-basica',
        'description' => 'Conteúdos iniciais',
        'is_active' => true,
    ]);
})->with(['admin' => 'admin', 'staff' => 'staff']);

it('forbids teachers from the category manager page and Livewire actions', function () {
    $teacher = User::factory()->teacher()->create();
    $this->actingAs($teacher);

    $this->get('/painel/categorias')->assertForbidden();

    Livewire::test(CategoryManager::class)
        ->assertForbidden();
});

it('rejects duplicate slugs without persisting a category', function () {
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

it('updates and toggles categories without deleting them', function () {
    $staff = User::factory()->staff()->create();
    $category = Category::factory()->create([
        'name' => 'Ciências',
        'slug' => 'ciencias',
        'is_active' => true,
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
    $this->assertModelExists($category->fresh());
});

it('searches categories by name or slug', function () {
    $staff = User::factory()->staff()->create();
    $match = Category::factory()->create(['name' => 'História Antiga', 'slug' => 'historia-antiga']);
    $unmatched = Category::factory()->create(['name' => 'Geografia', 'slug' => 'geografia']);
    $this->actingAs($staff);

    Livewire::test(CategoryManager::class)
        ->set('search', 'historia-antiga')
        ->assertSee($match->name)
        ->assertDontSee($unmatched->name);
});

it('renders the category manager route for staff', function () {
    $this->actingAs(User::factory()->staff()->create())
        ->get('/painel/categorias')
        ->assertOk()
        ->assertSee('Categorias');
});
