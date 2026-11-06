<?php

use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Livewire\Materials\MaterialDetail;
use App\Livewire\Materials\MaterialLibrary;
use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Livewire\Livewire;

it('allows teachers to browse published materials only', function () {
    $teacher = User::factory()->teacher()->create();
    $published = Material::factory()->published()->create(['title' => 'Material disponível']);
    $draft = Material::factory()->draft()->create(['title' => 'Material interno']);
    $archived = Material::factory()->archived()->create(['title' => 'Material arquivado']);
    $this->actingAs($teacher);

    Livewire::test(MaterialLibrary::class)
        ->assertSee($published->title)
        ->assertDontSee($draft->title)
        ->assertDontSee($archived->title);
});

it('opens published material details in the mobile library modal and records the access', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create([
        'title' => 'Material para consulta rápida',
        'description' => 'Descrição exibida no modal.',
    ]);
    $version = MaterialVersion::factory()->for($material)->create();
    $material->update(['current_version_id' => $version->getKey()]);
    $this->actingAs($teacher);

    Livewire::test(MaterialLibrary::class)
        ->call('openMobileMaterialDetail', $material->getKey())
        ->assertSet('showMobileMaterialDetail', true)
        ->assertSet('mobileMaterialId', $material->getKey())
        ->assertSee($material->description)
        ->assertSee('aria-label="Visualizar conteúdo"', false)
        ->assertSee(route('downloads.show', $material), false);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $teacher->getKey(),
        'material_id' => $material->getKey(),
        'action' => ActivityAction::MATERIAL_PREVIEWED->value,
        'description' => 'Material consultado.',
    ]);
});

it('allows staff to browse published materials and forbids administrators from the library', function () {
    $published = Material::factory()->published()->create(['title' => 'Conteúdo para a gestão']);

    $this->actingAs(User::factory()->staffInSchool()->create());

    Livewire::withoutLazyLoading();
    $this->get('/painel/biblioteca')
        ->assertOk()
        ->assertSee($published->title);
    Livewire::test(MaterialLibrary::class)->assertSee($published->title);

    $this->actingAs(User::factory()->admin()->create());

    $this->get('/painel/biblioteca')->assertForbidden();
    Livewire::test(MaterialLibrary::class)->assertForbidden();
});

it('allows staff to edit its own published material from the library card', function () {
    $staff = User::factory()->staffInSchool()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $material = Material::factory()->published()->for($category)->create([
        'created_by' => $staff->getKey(),
        'title' => 'Material original',
        'author' => 'Autoria original',
    ]);

    Livewire::actingAs($staff)
        ->test(MaterialLibrary::class)
        ->call('openEditMaterial', $material->getKey())
        ->assertSet('showEditForm', true)
        ->assertSee('Editar material')
        ->set('editForm.title', 'Material atualizado')
        ->set('editForm.author', 'Autoria atualizada')
        ->set('editForm.category_id', (string) $category->getKey())
        ->call('saveEditMaterial')
        ->assertHasNoErrors()
        ->assertSet('showEditForm', false);

    $this->assertDatabaseHas('materials', [
        'id' => $material->getKey(),
        'title' => 'Material atualizado',
        'author' => 'Autoria atualizada',
    ]);
});

it('filters the teacher library by search and category', function () {
    $teacher = User::factory()->teacher()->create();
    $category = Category::factory()->create();
    $matching = Material::factory()->published()->for($category)->create([
        'title' => 'Busca publicada',
        'author' => 'Autora',
    ]);
    $other = Material::factory()->published()->create(['title' => 'Outra categoria publicada']);
    $this->actingAs($teacher);

    Livewire::test(MaterialLibrary::class)
        ->set('search', 'Busca publicada')
        ->set('categoryFilter', (string) $category->getKey())
        ->assertSee($matching->title)
        ->assertDontSee($other->title);
});

it('filters published materials by author', function () {
    $teacher = User::factory()->teacher()->create();
    $matching = Material::factory()->published()->create([
        'title' => 'Material da equipe',
        'author' => 'Equipe de Robótica',
    ]);
    Material::factory()->published()->create([
        'title' => 'Material de outra autoria',
        'author' => 'Outra autoria',
    ]);

    Livewire::actingAs($teacher)
        ->test(MaterialLibrary::class)
        ->set('authorFilter', 'Equipe de Robótica')
        ->assertSee($matching->title)
        ->assertDontSee('Material de outra autoria');
});

it('persists library filters in the query string', function () {
    $teacher = User::factory()->teacher()->create();

    Livewire::withQueryParams([
        'q' => 'Arduino',
        'authorFilter' => 'Equipe de Robótica',
        'titleSortDirection' => 'desc',
    ])
        ->actingAs($teacher)
        ->test(MaterialLibrary::class)
        ->assertSet('search', 'Arduino')
        ->assertSet('authorFilter', 'Equipe de Robótica')
        ->assertSet('titleSortDirection', 'desc');
});

it('shows the category selector to library users', function () {
    $category = Category::factory()->create(['name' => 'Robótica educacional']);
    Material::factory()->published()->for($category)->create();

    Livewire::actingAs(User::factory()->staffInSchool()->create())
        ->test(MaterialLibrary::class)
        ->assertSee('Todas as categorias')
        ->assertSee($category->name);

    Livewire::actingAs(User::factory()->teacher()->create())
        ->test(MaterialLibrary::class)
        ->assertSee('Todas as categorias')
        ->assertSee($category->name);
});

it('stores and reapplies recent material searches', function () {
    $teacher = User::factory()->teacher()->create();

    Livewire::actingAs($teacher)
        ->test(MaterialLibrary::class)
        ->set('search', 'Sensores')
        ->assertSessionHas('material_library_recent_searches', ['Sensores'])
        ->set('search', '')
        ->call('applyRecentSearch', 'Sensores')
        ->assertSet('search', 'Sensores');
});

it('searches published materials by author without exposing unpublished materials', function () {
    $teacher = User::factory()->teacher()->create();
    $published = Material::factory()->published()->create([
        'title' => 'Título sem correspondência',
        'author' => 'Autora Magnética',
    ]);
    $draft = Material::factory()->draft()->create([
        'title' => 'Rascunho oculto',
        'author' => 'Autora Magnética',
    ]);
    $archived = Material::factory()->archived()->create([
        'title' => 'Arquivado oculto',
        'author' => 'Autora Magnética',
    ]);
    $this->actingAs($teacher);

    Livewire::test(MaterialLibrary::class)
        ->set('search', 'magnética')
        ->assertSee($published->title)
        ->assertDontSee($draft->title)
        ->assertDontSee($archived->title);
});

it('falls back to ascending title ordering for an invalid direction', function () {
    $teacher = User::factory()->teacher()->create();
    Material::factory()->published()->create(['title' => 'Zebra']);
    Material::factory()->published()->create(['title' => 'Abacaxi']);
    $this->actingAs($teacher);

    Livewire::test(MaterialLibrary::class)
        ->set('titleSortDirection', 'created_by')
        ->assertSet('titleSortDirection', 'asc')
        ->assertViewHas('materials', function ($materials): bool {
            return $materials->getCollection()->pluck('title')->values()->all() === ['Abacaxi', 'Zebra'];
        });
});

it('orders titles in both directions', function (string $titleSortDirection, array $expectedTitles) {
    $teacher = User::factory()->teacher()->create();
    $alpha = Material::factory()->published()->create([
        'title' => 'Alpha',
        'author' => 'Zelda',
        'created_at' => now()->subDays(2),
    ]);
    $beta = Material::factory()->published()->create([
        'title' => 'Beta',
        'author' => 'Alice',
        'created_at' => now(),
    ]);
    Livewire::actingAs($teacher)
        ->test(MaterialLibrary::class)
        ->set('titleSortDirection', $titleSortDirection)
        ->assertViewHas('materials', function ($materials) use ($expectedTitles): bool {
            return $materials->getCollection()->pluck('title')->values()->all() === $expectedTitles;
        });
})->with([
    'title A-Z' => ['asc', ['Alpha', 'Beta']],
    'title Z-A' => ['desc', ['Beta', 'Alpha']],
]);

it('opens the selected category from the teacher category library as a filtered materials list', function () {
    $teacher = User::factory()->teacher()->create();
    $category = Category::factory()->create();
    $matching = Material::factory()->published()->for($category)->create([
        'title' => 'Material da categoria selecionada',
    ]);
    $other = Material::factory()->published()->create(['title' => 'Material de outra categoria']);
    $this->actingAs($teacher);

    Livewire::withoutLazyLoading();
    $this->get(route('painel.library', ['categoryFilter' => $category->getKey()]))
        ->assertOk()
        ->assertSee($matching->title)
        ->assertDontSee($other->title);
});

it('keeps the category filter when opening material details in the library modal', function () {
    $teacher = User::factory()->teacher()->create();
    $category = Category::factory()->create();
    $material = Material::factory()->published()->for($category)->create();
    $this->actingAs($teacher);

    Livewire::withQueryParams(['categoryFilter' => $category->getKey()])
        ->actingAs($teacher)
        ->test(MaterialLibrary::class)
        ->call('openMobileMaterialDetail', $material->getKey())
        ->assertSet('categoryFilter', (string) $category->getKey())
        ->assertSet('showMobileMaterialDetail', true)
        ->assertSet('mobileMaterialId', $material->getKey());
});

it('shows published material details and an enabled download link to teachers', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create();
    $material->update(['current_version_id' => $version->getKey()]);
    $this->actingAs($teacher);

    $this->get(route('painel.library.show', $material))
        ->assertOk()
        ->assertSee($material->title)
        ->assertSee('aria-label="Baixar '.$material->title.'"', false)
        ->assertSee(route('downloads.show', $material), false);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $teacher->getKey(),
        'material_id' => $material->getKey(),
        'action' => ActivityAction::MATERIAL_PREVIEWED->value,
        'description' => 'Material consultado.',
    ]);

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->assertSee($material->title);
});

it('allows staff to view global material details without managing materials from others', function () {
    $staff = User::factory()->staffInSchool()->create();
    $otherCreator = User::factory()->admin()->create();
    $material = Material::factory()->published()->create([
        'created_by' => $otherCreator->getKey(),
    ]);
    $this->actingAs($staff);

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->assertSee($material->title)
        ->assertDontSee('Editar material')
        ->assertDontSee('Enviar nova versão');
});

it('forbids teachers from viewing unpublished material details', function (MaterialStatus $status) {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->create(['status' => $status]);
    $this->actingAs($teacher);

    $this->get(route('painel.library.show', $material))->assertForbidden();
})->with([
    'draft' => MaterialStatus::DRAFT,
    'archived' => MaterialStatus::ARCHIVED,
]);

it('renders the published library route for teachers', function () {
    $this->actingAs(User::factory()->teacher()->create())
        ->get('/painel/biblioteca')
        ->assertOk()
        ->assertSee('aria-label="Carregando biblioteca de materiais"', false)
        ->assertDontSee('data-app-loading-skeleton', false);
});
