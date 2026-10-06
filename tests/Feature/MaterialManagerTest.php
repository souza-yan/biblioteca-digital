<?php

use App\Enums\MaterialStatus;
use App\Livewire\Materials\MaterialManager;
use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

it('allows admins and staff to create materials with the authenticated creator', function (string $role) {
    $actor = match ($role) {
        'admin' => User::factory()->admin()->create(),
        'staff' => User::factory()->staff()->create(),
    };
    $category = Category::factory()->create();
    $otherUser = User::factory()->admin()->create();
    $this->actingAs($actor);

    Livewire::test(MaterialManager::class)
        ->call('openCreate')
        ->set('form.title', 'Material de Ciências')
        ->set('form.description', 'Conteúdo introdutório')
        ->set('form.category_id', (string) $category->getKey())
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Escolar')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('materials', [
        'title' => 'Material de Ciências',
        'category_id' => $category->getKey(),
        'created_by' => $actor->getKey(),
        'status' => MaterialStatus::DRAFT->value,
    ]);
    $this->assertDatabaseMissing('materials', ['created_by' => $otherUser->getKey()]);
})->with(['admin' => 'admin', 'staff' => 'staff']);

it('forbids teachers from accessing material management', function () {
    $this->actingAs(User::factory()->teacher()->create());

    $this->get('/painel/materiais')->assertForbidden();

    Livewire::test(MaterialManager::class)->assertForbidden();
});

it('rejects an inactive category in the material form', function () {
    $category = Category::factory()->inactive()->create();
    $this->actingAs(User::factory()->staff()->create());

    Livewire::test(MaterialManager::class)
        ->call('openCreate')
        ->set('form.title', 'Material inválido')
        ->set('form.category_id', (string) $category->getKey())
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Escolar')
        ->call('save')
        ->assertHasErrors('form.category_id');

    $this->assertDatabaseMissing('materials', ['title' => 'Material inválido']);
});

it('filters managed materials by status and category', function () {
    $staff = User::factory()->staff()->create();
    $category = Category::factory()->create();
    $matching = Material::factory()->published()->for($category)->create(['title' => 'Publicado da categoria']);
    $otherCategory = Category::factory()->create();
    $wrongCategory = Material::factory()->draft()->for($otherCategory)->create(['title' => 'Rascunho de outra categoria']);
    $wrongStatus = Material::factory()->draft()->for($category)->create(['title' => 'Rascunho da categoria']);
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->set('statusFilter', MaterialStatus::PUBLISHED->value)
        ->set('categoryFilter', (string) $category->getKey())
        ->assertSee($matching->title)
        ->assertDontSee($wrongCategory->title)
        ->assertDontSee($wrongStatus->title);
});

it('updates a material using an active category', function () {
    $staff = User::factory()->staff()->create();
    $material = Material::factory()->create(['title' => 'Título antigo']);
    $category = Category::factory()->create();
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->call('editMaterial', $material->getKey())
        ->set('form.title', 'Título atualizado')
        ->set('form.category_id', (string) $category->getKey())
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('materials', [
        'id' => $material->getKey(),
        'title' => 'Título atualizado',
        'category_id' => $category->getKey(),
    ]);
});

it('prevents clients from changing the material manager target id', function (string $property) {
    $staff = User::factory()->staff()->create();
    $material = Material::factory()->create(['title' => 'Material original']);
    $otherMaterial = Material::factory()->create(['title' => 'Outro material']);
    $this->actingAs($staff);

    $component = Livewire::test(MaterialManager::class)
        ->call('editMaterial', $material->getKey())
        ->assertSet('editingMaterialId', $material->getKey())
        ->assertSet('form.materialId', $material->getKey());

    expect(fn () => $component->set($property, $otherMaterial->getKey()))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    $component->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas('materials', [
        'id' => $otherMaterial->getKey(),
        'title' => 'Outro material',
    ]);
})->with([
    'component id' => 'editingMaterialId',
    'form id' => 'form.materialId',
]);

it('shows the missing current version error when publishing and allows archive', function () {
    $staff = User::factory()->staff()->create();
    $withoutVersion = Material::factory()->draft()->create();
    $toArchive = Material::factory()->draft()->create();
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->call('publish', $withoutVersion->getKey())
        ->assertHasErrors('publish')
        ->call('archive', $toArchive->getKey())
        ->assertHasNoErrors();

    $this->assertDatabaseHas('materials', [
        'id' => $withoutVersion->getKey(),
        'status' => MaterialStatus::DRAFT->value,
    ]);
    $this->assertDatabaseHas('materials', [
        'id' => $toArchive->getKey(),
        'status' => MaterialStatus::ARCHIVED->value,
    ]);
});

it('publishes a material when it has a current version', function () {
    $staff = User::factory()->staff()->create();
    $material = Material::factory()->draft()->create();
    $version = MaterialVersion::factory()->for($material)->create();
    $material->update(['current_version_id' => $version->getKey()]);
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->call('publish', $material->getKey())
        ->assertHasNoErrors();

    $this->assertDatabaseHas('materials', [
        'id' => $material->getKey(),
        'status' => MaterialStatus::PUBLISHED->value,
    ]);
});

it('renders the material manager route for staff', function () {
    $this->actingAs(User::factory()->staff()->create())
        ->get('/painel/materiais')
        ->assertOk()
        ->assertSee('Materiais');
});
