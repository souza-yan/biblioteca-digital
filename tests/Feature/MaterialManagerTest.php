<?php

use App\Enums\MaterialStatus;
use App\Livewire\Categories\CategoryLibrary;
use App\Livewire\Materials\MaterialManager;
use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

it('allows admins to create materials with the authenticated creator', function (string $role) {
    Storage::fake('local');
    $actor = match ($role) {
        'admin' => User::factory()->admin()->create(),
        'staff' => User::factory()->admin()->create(),
    };
    $category = Category::factory()->create();
    $otherUser = User::factory()->admin()->create();
    $this->actingAs($actor);

    Livewire::test(MaterialManager::class)
        ->assertDontSee('Acervo global')
        ->call('openCreate')
        ->set('form.title', 'Material de Ciências')
        ->set('form.description', 'Conteúdo introdutório')
        ->set('form.category_id', (string) $category->getKey())
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Escolar')
        ->set('form.file', UploadedFile::fake()->create('material.pdf', 100, 'application/pdf'))
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('materials', [
        'title' => 'Material de Ciências',
        'category_id' => $category->getKey(),
        'created_by' => $actor->getKey(),
        'status' => MaterialStatus::DRAFT->value,
    ]);
    $this->assertDatabaseMissing('materials', ['created_by' => $otherUser->getKey()]);
})->with(['admin' => 'admin']);

it('forbids teachers from accessing material management', function () {
    $this->actingAs(User::factory()->teacher()->create());

    $this->get('/painel/materiais')->assertForbidden();

    Livewire::test(MaterialManager::class)->assertForbidden();
});

it('rejects an inactive category in the material form', function () {
    $category = Category::factory()->inactive()->create();
    $this->actingAs(User::factory()->admin()->create());

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
    $staff = User::factory()->admin()->create();
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

it('filters managed materials to only the selected status', function (
    MaterialStatus $status,
    string $visible,
    array $hidden,
) {
    $staff = User::factory()->admin()->create();
    Material::factory()->draft()->create(['title' => 'Filtro: rascunho']);
    Material::factory()->published()->create(['title' => 'Filtro: publicado']);
    Material::factory()->archived()->create(['title' => 'Filtro: arquivado']);
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->set('statusFilter', $status->value)
        ->assertSee($visible)
        ->assertDontSee($hidden[0])
        ->assertDontSee($hidden[1]);
})->with([
    'draft' => [MaterialStatus::DRAFT, 'Filtro: rascunho', ['Filtro: publicado', 'Filtro: arquivado']],
    'published' => [MaterialStatus::PUBLISHED, 'Filtro: publicado', ['Filtro: rascunho', 'Filtro: arquivado']],
    'archived' => [MaterialStatus::ARCHIVED, 'Filtro: arquivado', ['Filtro: rascunho', 'Filtro: publicado']],
]);

it('defaults the managed materials list to published materials', function () {
    $staff = User::factory()->admin()->create();
    Material::factory()->draft()->create(['title' => 'Listagem em rascunho']);
    Material::factory()->published()->create(['title' => 'Listagem publicada']);
    Material::factory()->archived()->create(['title' => 'Listagem arquivada']);
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->assertSet('statusFilter', MaterialStatus::PUBLISHED->value)
        ->assertDontSee('Listagem em rascunho')
        ->assertSee('Listagem publicada')
        ->assertDontSee('Listagem arquivada');
});

it('renders the all-status option and each material status in the list filter', function () {
    Livewire::actingAs(User::factory()->admin()->create())
        ->test(MaterialManager::class)
        ->assertViewHas('statusOptions', [
            ['label' => MaterialStatus::DRAFT->label(), 'value' => MaterialStatus::DRAFT->value],
            ['label' => MaterialStatus::PUBLISHED->label(), 'value' => MaterialStatus::PUBLISHED->value],
            ['label' => MaterialStatus::ARCHIVED->label(), 'value' => MaterialStatus::ARCHIVED->value],
        ]);
});

it('ignores an invalid status filter and returns to published materials', function () {
    $staff = User::factory()->admin()->create();
    Material::factory()->draft()->create(['title' => 'Status inválido: rascunho']);
    Material::factory()->published()->create(['title' => 'Status inválido: publicado']);
    Material::factory()->archived()->create(['title' => 'Status inválido: arquivado']);
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->set('statusFilter', 'xyz')
        ->assertHasNoErrors()
        ->assertSee('Status inválido: publicado')
        ->assertDontSee('Status inválido: rascunho')
        ->assertDontSee('Status inválido: arquivado');
});

it('resets the materials list to page one when its status filter changes', function () {
    $staff = User::factory()->admin()->create();
    Material::factory()->published()->count(16)->create();
    Material::factory()->draft()->create(['title' => 'Rascunho após voltar à primeira página']);
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->call('gotoPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('statusFilter', MaterialStatus::DRAFT->value)
        ->assertSet('paginators.page', 1)
        ->assertSee('Rascunho após voltar à primeira página');
});

it('updates a material using an active category', function () {
    $staff = User::factory()->admin()->create();
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
    $staff = User::factory()->admin()->create();
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
    $staff = User::factory()->admin()->create();
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
    $staff = User::factory()->admin()->create();
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

it('renders the material manager route for admins', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/painel/materiais')
        ->assertOk()
        ->assertSee('Materiais');
});

it('paginates published materials by ten for staff', function () {
    $staff = User::factory()->staffInSchool()->create();
    Material::factory()->published()->count(11)->create();

    Livewire::actingAs($staff)
        ->test(MaterialManager::class)
        ->assertViewHas('materials', fn ($materials): bool => $materials->perPage() === 10 && $materials->total() === 11)
        ->assertSee('wire:click="nextPage(\'page\')"', false);
});

it('shows the material creation flow to staff as a draft submission', function () {
    $staff = User::factory()->staffInSchool()->create();

    Livewire::actingAs($staff)
        ->test(MaterialManager::class)
        ->assertSee('Visualizar conteúdos')
        ->assertDontSee('Novo material')
        ->call('openCreate')
        ->assertSet('showForm', true)
        ->assertSee('Enviado como rascunho')
        ->assertSee('O material ficará disponível para revisão do administrador.');
});

it('opens the staff material form from the category library action', function () {
    $staff = User::factory()->staffInSchool()->create();

    Livewire::actingAs($staff)
        ->test(CategoryLibrary::class)
        ->call('openMaterialForm')
        ->assertSet('showMaterialForm', true)
        ->assertSee('Novo material')
        ->assertSee('Enviado como rascunho');
});

it('shows staff a download action for published materials with a current version', function () {
    $staff = User::factory()->staffInSchool()->create();
    $material = Material::factory()->published()->create(['title' => 'Material disponível para baixar']);
    $version = MaterialVersion::factory()->for($material)->create();
    $material->update(['current_version_id' => $version->getKey()]);

    Livewire::actingAs($staff)
        ->test(MaterialManager::class)
        ->assertSee('Baixar '.$material->title)
        ->assertSee(route('downloads.show', $material), false);
});

it('uses the edit button to open material details instead of making the title a link', function () {
    $admin = User::factory()->admin()->create();
    $material = Material::factory()->published()->create(['title' => 'Material para editar']);

    Livewire::actingAs($admin)
        ->test(MaterialManager::class)
        ->assertSee(route('painel.materials.show', $material), false)
        ->assertSee('Editar material '.$material->title)
        ->assertDontSee('<a class="text-blue-700 hover:underline"', false);
});
