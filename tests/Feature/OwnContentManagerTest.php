<?php

use App\Livewire\Categories\CategoryManager;
use App\Livewire\Materials\MaterialManager;
use App\Livewire\Materials\OwnContentManager;
use App\Livewire\NavigationMenu;
use App\Models\Category;
use App\Models\Material;
use App\Models\User;
use Livewire\Livewire;

it('combines both management lists on the staff page without displaying other creators content', function () {
    $staff = User::factory()->staffInSchool()->create();
    $category = Category::factory()->create(['created_by' => $staff->getKey()]);
    $material = Material::factory()->published()->create(['created_by' => $staff->getKey()]);
    $otherCategory = Category::factory()->create();
    $otherMaterial = Material::factory()->published()->create();

    $this->actingAs($staff)->get(route('painel.manage-content'))
        ->assertOk()->assertSee('Gerenciar Materiais')
        ->assertSeeLivewire(MaterialManager::class)->assertSeeLivewire(CategoryManager::class)
        ->assertSee($category->name)->assertSee($material->title)
        ->assertDontSee('Editar categoria '.$otherCategory->name)->assertDontSee($otherMaterial->title)
        ->assertSee('id="managed-material-search"', false)->assertSee('id="managed-category-search"', false)
        ->assertDontSee('wire:click="openCreate"', false);
});

it('provides one sidebar entry for the combined management page', function () {
    $staff = User::factory()->staffInSchool()->create();

    Livewire::actingAs($staff)->test(NavigationMenu::class)
        ->assertSee('Gerenciar Materiais')->assertSee(route('painel.manage-content'), false)
        ->assertDontSee('Meus materiais')->assertDontSee('Minhas categorias');
});

it('keeps embedded category pagination separate from materials pagination', function () {
    $staff = User::factory()->staffInSchool()->create();
    Category::factory()->count(16)->create(['created_by' => $staff->getKey()]);

    Livewire::actingAs($staff)->test(CategoryManager::class, ['embedded' => true])
        ->assertViewHas('categories', fn ($categories): bool => $categories->getPageName() === 'categoriesPage')
        ->call('gotoPage', 2, 'categoriesPage')->assertSet('paginators.categoriesPage', 2)
        ->set('search', 'Categoria inexistente')->assertSet('paginators.categoriesPage', 1);
});

it('requires authentication for the combined page', function () {
    $this->get(route('painel.manage-content'))->assertRedirect(route('login'));
});

it('denies the staff page to administrators and teachers including direct component access', function (string $role) {
    $user = $role === 'admin' ? User::factory()->admin()->create() : User::factory()->teacher()->create();

    $this->actingAs($user)->get(route('painel.manage-content'))->assertForbidden();
    Livewire::actingAs($user)->test(OwnContentManager::class)->assertForbidden();
})->with(['admin', 'teacher']);

it('denies staff without exactly one active school', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)->get(route('painel.manage-content'))->assertForbidden();
    Livewire::actingAs($staff)->test(OwnContentManager::class)->assertForbidden();
});

it('rechecks school membership when an already opened management page refreshes', function () {
    $staff = User::factory()->staffInSchool()->create();
    $component = Livewire::actingAs($staff)->test(OwnContentManager::class);

    $staff->schools()->updateExistingPivot($staff->school()->getKey(), ['is_active' => false]);
    $staff->forgetSchoolContext();

    $component->call('$refresh')->assertForbidden();
});

it('rejects inactive staff on direct component access', function () {
    $staff = User::factory()->staffInSchool()->create(['is_active' => false]);

    Livewire::actingAs($staff)->test(OwnContentManager::class)->assertForbidden();
});
