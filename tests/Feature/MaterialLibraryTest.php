<?php

use App\Enums\MaterialStatus;
use App\Livewire\Materials\MaterialDetail;
use App\Livewire\Materials\MaterialLibrary;
use App\Models\Category;
use App\Models\Download;
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

it('allows staff to browse published materials and forbids administrators from the library', function () {
    $published = Material::factory()->published()->create(['title' => 'Conteúdo para a gestão']);

    $this->actingAs(User::factory()->staffInSchool()->create());

    $this->get('/painel/biblioteca')
        ->assertOk()
        ->assertSee($published->title);
    Livewire::test(MaterialLibrary::class)->assertSee($published->title);

    $this->actingAs(User::factory()->admin()->create());

    $this->get('/painel/biblioteca')->assertForbidden();
    Livewire::test(MaterialLibrary::class)->assertForbidden();
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

it('filters published materials by format author and publication period', function () {
    $teacher = User::factory()->teacher()->create();
    $matching = Material::factory()->published()->create([
        'title' => 'Apostila recente',
        'type' => 'pdf',
        'author' => 'Equipe de Robótica',
        'published_at' => now()->subDays(5),
    ]);
    Material::factory()->published()->create([
        'title' => 'Vídeo recente',
        'type' => 'mp4',
        'author' => 'Equipe de Robótica',
        'published_at' => now()->subDays(5),
    ]);
    Material::factory()->published()->create([
        'title' => 'Apostila antiga',
        'type' => 'pdf',
        'author' => 'Equipe de Robótica',
        'published_at' => now()->subDays(120),
    ]);

    Livewire::actingAs($teacher)
        ->test(MaterialLibrary::class)
        ->set('formatFilter', 'pdf')
        ->set('authorFilter', 'Equipe de Robótica')
        ->set('publishedPeriod', '30')
        ->assertSee($matching->title)
        ->assertDontSee('Vídeo recente')
        ->assertDontSee('Apostila antiga');
});

it('persists library filters in the query string and can clear them', function () {
    $teacher = User::factory()->teacher()->create();

    Livewire::withQueryParams([
        'q' => 'Arduino',
        'formatFilter' => 'pdf',
        'sortOrder' => 'recent',
    ])
        ->actingAs($teacher)
        ->test(MaterialLibrary::class)
        ->assertSet('search', 'Arduino')
        ->assertSet('formatFilter', 'pdf')
        ->assertSet('sortOrder', 'recent')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('formatFilter', '')
        ->assertSet('sortOrder', 'title');
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

it('searches published materials by description without exposing unpublished materials', function () {
    $teacher = User::factory()->teacher()->create();
    $published = Material::factory()->published()->create([
        'title' => 'Título sem correspondência',
        'description' => 'Descrição especial sobre sensores magnéticos',
        'author' => 'Autora',
    ]);
    $draft = Material::factory()->draft()->create([
        'title' => 'Rascunho oculto',
        'description' => 'Descrição especial sobre sensores magnéticos',
    ]);
    $archived = Material::factory()->archived()->create([
        'title' => 'Arquivado oculto',
        'description' => 'Descrição especial sobre sensores magnéticos',
    ]);
    $this->actingAs($teacher);

    Livewire::test(MaterialLibrary::class)
        ->set('search', 'sensores magnéticos')
        ->assertSee($published->title)
        ->assertDontSee($draft->title)
        ->assertDontSee($archived->title);
});

it('falls back to title ordering for an invalid library sort option', function () {
    $teacher = User::factory()->teacher()->create();
    Material::factory()->published()->create(['title' => 'Zebra']);
    Material::factory()->published()->create(['title' => 'Abacaxi']);
    $this->actingAs($teacher);

    Livewire::test(MaterialLibrary::class)
        ->set('sortOrder', 'created_by')
        ->assertSet('sortOrder', 'title')
        ->assertViewHas('materials', function ($materials): bool {
            return $materials->getCollection()->pluck('title')->values()->all() === ['Abacaxi', 'Zebra'];
        });
});

it('supports each fixed library sort option', function (string $sortOrder, array $expectedTitles) {
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
    $version = MaterialVersion::factory()->for($alpha)->create();
    $alpha->update(['current_version_id' => $version->getKey()]);
    Download::factory()->count(2)->create([
        'material_id' => $alpha->getKey(),
        'material_version_id' => $version->getKey(),
    ]);

    Livewire::actingAs($teacher)
        ->test(MaterialLibrary::class)
        ->set('sortOrder', $sortOrder)
        ->assertViewHas('materials', function ($materials) use ($expectedTitles): bool {
            return $materials->getCollection()->pluck('title')->values()->all() === $expectedTitles;
        });
})->with([
    'title A-Z' => ['title', ['Alpha', 'Beta']],
    'most recent' => ['recent', ['Beta', 'Alpha']],
    'author' => ['author', ['Beta', 'Alpha']],
    'most downloaded' => ['most_downloaded', ['Alpha', 'Beta']],
]);

it('opens the selected category from the teacher category library as a filtered materials list', function () {
    $teacher = User::factory()->teacher()->create();
    $category = Category::factory()->create();
    $matching = Material::factory()->published()->for($category)->create([
        'title' => 'Material da categoria selecionada',
    ]);
    $other = Material::factory()->published()->create(['title' => 'Material de outra categoria']);
    $this->actingAs($teacher);

    $this->get(route('painel.library', ['categoryFilter' => $category->getKey()]))
        ->assertOk()
        ->assertSee($matching->title)
        ->assertDontSee($other->title);
});

it('preserves the category filter when opening material details from the library', function () {
    $teacher = User::factory()->teacher()->create();
    $category = Category::factory()->create();
    $material = Material::factory()->published()->for($category)->create();
    $this->actingAs($teacher);

    $filteredLibraryUrl = route('painel.library', ['categoryFilter' => $category->getKey()]);

    $this->get($filteredLibraryUrl)
        ->assertOk()
        ->assertSee(route('painel.library.show', [
            'material' => $material,
            'categoryFilter' => $category->getKey(),
        ]), false);

    $this->get(route('painel.library.show', [
        'material' => $material,
        'categoryFilter' => $category->getKey(),
    ]))
        ->assertOk()
        ->assertSee('Categorias')
        ->assertSee($filteredLibraryUrl, false);
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

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->assertSee($material->title);
});

it('hides publish and archive actions from staff material details and rejects direct calls', function () {
    $staff = User::factory()->staffInSchool()->create();
    $material = Material::factory()->published()->create();
    $this->actingAs($staff);

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->assertSee($material->title)
        ->assertDontSee('Publicar')
        ->assertDontSee('Arquivar');

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->call('publish')
        ->assertForbidden();

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->call('archive')
        ->assertForbidden();
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
        ->assertSee('Materiais publicados');
});
