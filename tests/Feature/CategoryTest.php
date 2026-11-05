<?php

use App\Actions\Category\ListCategoryOptions;
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

it('exposes only active categories with published materials to teachers', function () {
    $activeCategory = Category::factory()->create(['name' => 'Categoria ativa']);
    $inactiveCategory = Category::factory()->inactive()->create(['name' => 'Categoria inativa']);
    Material::factory()->published()->for($activeCategory)->create();
    Material::factory()->published()->for($inactiveCategory)->create();

    $this->actingAs(User::factory()->teacher()->create());

    Livewire::test(MaterialLibrary::class)
        ->assertViewHas(
            'categories',
            fn ($categories): bool => $categories->pluck('id')->all() === [$activeCategory->getKey()],
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

it('shows creation controls in the category library for staff', function () {
    $staff = User::factory()->staffInSchool()->create();
    $category = Category::factory()->create(['name' => 'Categoria da gestão']);
    $emptyCategory = Category::factory()->create(['name' => 'Categoria vazia da gestão']);
    Material::factory()->published()->for($category)->create();

    $this->actingAs($staff)
        ->get(route('painel.library.categories'))
        ->assertOk()
        ->assertSee($category->name)
        ->assertSee($emptyCategory->name)
        ->assertSee('Nova categoria')
        ->assertSee('Novo material');

    Livewire::actingAs($staff)
        ->test(CategoryLibrary::class)
        ->assertSee($category->name)
        ->assertSee($emptyCategory->name)
        ->assertSee('Nova categoria')
        ->assertSee('Novo material')
        ->call('openCategoryCreate')
        ->assertSet('showCategoryForm', true)
        ->call('openMaterialCreate')
        ->assertSet('showMaterialForm', true);
});

it('allows staff to create a category and material from the category library', function () {
    $staff = User::factory()->staffInSchool()->create();
    Storage::fake('local');

    $component = Livewire::actingAs($staff)
        ->test(CategoryLibrary::class)
        ->call('openCategoryCreate')
        ->set('categoryForm.name', 'Categoria criada na biblioteca')
        ->set('categoryForm.description', 'Descrição da nova categoria')
        ->call('saveCategory')
        ->assertHasNoErrors();

    $category = Category::query()
        ->where('name', 'Categoria criada na biblioteca')
        ->firstOrFail();

    $component
        ->call('openMaterialCreate')
        ->assertSet('showMaterialForm', true)
        ->assertSet('materialForm.category_id', (string) $category->getKey())
        ->assertSee($category->name)
        ->set('materialForm.title', 'Material criado na biblioteca')
        ->set('materialForm.description', 'Conteúdo da gestão')
        ->set('materialForm.author', 'Equipe Escolar')
        ->set('materialForm.file', UploadedFile::fake()->create('material.pdf', 100, 'application/pdf'))
        ->call('saveMaterial')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'Categoria criada na biblioteca',
        'is_active' => true,
        'created_by' => $staff->getKey(),
    ]);
    $this->assertDatabaseHas('materials', [
        'title' => 'Material criado na biblioteca',
        'category_id' => $category->getKey(),
        'created_by' => $staff->getKey(),
    ]);
});

it('allows staff to edit a category they created from the category library', function () {
    $staff = User::factory()->staffInSchool()->create();
    $category = Category::factory()->create([
        'name' => 'Categoria da própria gestão',
        'created_by' => $staff->getKey(),
    ]);

    Livewire::actingAs($staff)
        ->test(CategoryLibrary::class)
        ->call('editCategory', $category->getKey())
        ->assertSet('showCategoryForm', true)
        ->set('categoryForm.name', 'Categoria atualizada pela gestão')
        ->call('saveCategory')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'id' => $category->getKey(),
        'name' => 'Categoria atualizada pela gestão',
        'created_by' => $staff->getKey(),
    ]);
});

it('forbids staff from editing a category created by another manager', function () {
    $staff = User::factory()->staffInSchool()->create();
    $otherStaff = User::factory()->staffInSchool()->create();
    $category = Category::factory()->create(['created_by' => $otherStaff->getKey()]);

    Livewire::actingAs($staff)
        ->test(CategoryLibrary::class)
        ->call('editCategory', $category->getKey())
        ->assertForbidden();
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

it('allows staff to manage only categories they created', function () {
    $staff = User::factory()->staffInSchool()->create();
    $otherStaff = User::factory()->staffInSchool()->create();
    $ownCategory = Category::factory()->create(['created_by' => $staff->getKey()]);
    $otherCategory = Category::factory()->create(['created_by' => $otherStaff->getKey()]);

    expect(Gate::forUser($staff)->allows('create', Category::class))->toBeTrue()
        ->and(Gate::forUser($staff)->allows('update', $ownCategory))->toBeTrue()
        ->and(Gate::forUser($staff)->allows('toggleActive', $ownCategory))->toBeTrue()
        ->and(Gate::forUser($staff)->allows('update', $otherCategory))->toBeFalse()
        ->and(Gate::forUser($staff)->allows('toggleActive', $otherCategory))->toBeFalse();
});

it('allows administrators to manage categories from any creator', function () {
    $administrator = User::factory()->admin()->create();
    $category = Category::factory()->create(['created_by' => User::factory()->staffInSchool()->create()->getKey()]);

    expect(Gate::forUser($administrator)->allows('create', Category::class))->toBeTrue()
        ->and(Gate::forUser($administrator)->allows('update', $category))->toBeTrue()
        ->and(Gate::forUser($administrator)->allows('toggleActive', $category))->toBeTrue();
});

it('does not allow teachers to manage categories', function () {
    $teacher = User::factory()->teacher()->create();
    $category = Category::factory()->create();

    expect(Gate::forUser($teacher)->allows('create', Category::class))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('update', $category))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('toggleActive', $category))->toBeFalse();
});
