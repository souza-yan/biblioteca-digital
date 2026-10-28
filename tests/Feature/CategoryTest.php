<?php

use App\Actions\Category\ListCategoryOptions;
use App\Enums\MaterialStatus;
use App\Livewire\Categories\CategoryLibrary;
use App\Livewire\Categories\CategoryManager;
use App\Livewire\Materials\MaterialLibrary;
use App\Models\Category;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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
        ->assertDontSee('robotica-criativa')
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

it('allows staff to browse categories and access creation actions', function () {
    $staff = User::factory()->staffInSchool()->create();
    $category = Category::factory()->create(['name' => 'Categoria da gestão']);
    Material::factory()->published()->for($category)->create();

    $this->actingAs($staff)
        ->get(route('painel.library.categories'))
        ->assertOk()
        ->assertSee($category->name)
        ->assertSee('Nova categoria')
        ->assertSee('Novo material')
        ->assertSee('wire:click="openCategoryForm"', false)
        ->assertSee('wire:click="openMaterialForm"', false)
        ->assertDontSee(route('painel.materials', ['create' => 1]), false);

    Livewire::actingAs($staff)
        ->test(CategoryLibrary::class)
        ->call('openCategoryForm')
        ->assertSet('showCategoryForm', true)
        ->assertSee('Enviada como inativa')
        ->call('closeCategoryForm')
        ->call('openMaterialForm')
        ->assertSet('showMaterialForm', true)
        ->assertSee('Enviado como rascunho');
});

it('allows staff to create category and material from the category library modals', function () {
    Storage::fake('local');
    $staff = User::factory()->staffInSchool()->create();
    $activeCategory = Category::factory()->create();

    Livewire::actingAs($staff)
        ->test(CategoryLibrary::class)
        ->call('openCategoryForm')
        ->set('categoryForm.name', 'Tecnologia educacional')
        ->set('categoryForm.description', 'Recursos para a sala de aula.')
        ->call('saveCategory')
        ->assertHasNoErrors()
        ->assertSet('showCategoryForm', false)
        ->call('openMaterialForm')
        ->set('materialForm.title', 'Guia de tecnologia')
        ->set('materialForm.category_id', (string) $activeCategory->getKey())
        ->set('materialForm.author', 'Equipe pedagógica')
        ->set('materialForm.file', UploadedFile::fake()->create('guia.pdf', 100, 'application/pdf'))
        ->call('saveMaterial')
        ->assertHasNoErrors()
        ->assertSet('showMaterialForm', false);

    $this->assertDatabaseHas('categories', [
        'name' => 'Tecnologia educacional',
        'slug' => 'tecnologia-educacional',
        'is_active' => false,
    ]);
    $this->assertDatabaseHas('materials', [
        'title' => 'Guia de tecnologia',
        'created_by' => $staff->getKey(),
        'status' => MaterialStatus::DRAFT->value,
    ]);
});

it('allows admins to create a category with a generated slug', function () {
    $this->actingAs(User::factory()->admin()->create());

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
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CategoryManager::class)
        ->call('openCreate')
        ->set('form.name', 'Outra categoria')
        ->set('form.slug', 'slug-em-uso')
        ->call('save')
        ->assertHasErrors('form.slug');

    $this->assertDatabaseMissing('categories', ['name' => 'Outra categoria']);
});

it('allows admins to update and deactivate a category', function () {
    $staff = User::factory()->admin()->create();
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

it('invalidates cached category options after a category changes', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Nome original']);
    $categoryOptions = app(ListCategoryOptions::class);

    expect($categoryOptions->all()->firstWhere('id', $category->getKey())?->name)
        ->toBe('Nome original');

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('editCategory', $category->getKey())
        ->set('form.name', 'Nome atualizado')
        ->call('save')
        ->assertHasNoErrors();

    expect($categoryOptions->all()->firstWhere('id', $category->getKey())?->name)
        ->toBe('Nome atualizado');
});

it('rebuilds category options when the cached payload is incompatible', function () {
    $category = Category::factory()->create(['name' => 'Categoria recuperada']);
    Cache::forever('category-options.v2.all', new stdClass);

    $categories = app(ListCategoryOptions::class)->all();

    expect($categories)
        ->toBeInstanceOf(Collection::class)
        ->and($categories->firstWhere('id', $category->getKey())?->name)
        ->toBe('Categoria recuperada')
        ->and(Cache::get('category-options.v2.all'))
        ->toBeArray();
});

it('allows staff to submit categories while keeping updates and activation exclusive to admins', function (string $role, bool $canCreate, bool $canManage) {
    $user = match ($role) {
        'admin' => User::factory()->admin()->create(),
        'staff' => User::factory()->staffInSchool()->create(),
        default => User::factory()->teacher()->create(),
    };
    $category = Category::factory()->make();

    expect(Gate::forUser($user)->allows('create', Category::class))->toBe($canCreate)
        ->and(Gate::forUser($user)->allows('update', $category))->toBe($canManage)
        ->and(Gate::forUser($user)->allows('toggleActive', $category))->toBe($canManage);
})->with([
    'admin' => ['admin', true, true],
    'staff' => ['staff', true, false],
    'teacher' => ['teacher', false, false],
]);
