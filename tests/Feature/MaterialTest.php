<?php

use App\Enums\MaterialStatus;
use App\Livewire\Materials\MaterialDetail;
use App\Livewire\Materials\MaterialLibrary;
use App\Livewire\Materials\MaterialManager;
use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('shows teachers only published materials in the library', function () {
    $published = Material::factory()->published()->create(['title' => 'Material publicado']);
    $draft = Material::factory()->draft()->create(['title' => 'Material em rascunho']);
    $archived = Material::factory()->archived()->create(['title' => 'Material arquivado']);
    $this->actingAs(User::factory()->teacher()->create());

    Livewire::test(MaterialLibrary::class)
        ->assertSee($published->title)
        ->assertDontSee($draft->title)
        ->assertDontSee($archived->title);
});

it('forbids teachers from opening draft material details', function () {
    $material = Material::factory()->draft()->create();
    $this->actingAs(User::factory()->teacher()->create());

    $this->get(route('painel.library.show', $material))->assertForbidden();
    Livewire::test(MaterialDetail::class, ['material' => $material])->assertForbidden();
});

it('allows staff to publish a material owned by the authenticated user', function () {
    Storage::fake('local');
    $staff = User::factory()->staffInSchool()->create();
    $category = Category::factory()->create();
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->call('openCreate')
        ->set('form.title', 'Apostila de Ciências')
        ->set('form.description', 'Material introdutório.')
        ->set('form.category_id', (string) $category->getKey())
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Pedagógica')
        ->set('form.file', UploadedFile::fake()->create('material.pdf', 100, 'application/pdf'))
        ->set('form.initialStatus', MaterialStatus::PUBLISHED->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('statusFilter', MaterialStatus::PUBLISHED->value);

    $this->assertDatabaseHas('materials', [
        'title' => 'Apostila de Ciências',
        'category_id' => $category->getKey(),
        'created_by' => $staff->getKey(),
        'status' => MaterialStatus::PUBLISHED->value,
    ]);
});

it('forbids teachers from material management', function () {
    $this->actingAs(User::factory()->teacher()->create());

    $this->get(route('painel.materials'))->assertForbidden();
    Livewire::test(MaterialManager::class)->assertForbidden();
});

it('publishes materials with a current version and records the timestamp', function () {
    $staff = User::factory()->admin()->create();
    $material = Material::factory()->draft()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'published_by' => $staff->getKey(),
    ]);
    $material->update(['current_version_id' => $version->getKey()]);
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->call('publish', $material->getKey())
        ->assertHasNoErrors();

    expect($material->fresh()->status)->toBe(MaterialStatus::PUBLISHED);
    expect($material->fresh()->published_at)->not->toBeNull();
});

it('rejects publishing without a current version and allows archiving', function () {
    $staff = User::factory()->admin()->create();
    $withoutVersion = Material::factory()->draft()->create();
    $toArchive = Material::factory()->draft()->create();
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->call('publish', $withoutVersion->getKey())
        ->assertHasErrors('publish')
        ->call('archive', $toArchive->getKey())
        ->assertHasNoErrors();

    expect($withoutVersion->fresh()->status)->toBe(MaterialStatus::DRAFT);
    expect($toArchive->fresh()->status)->toBe(MaterialStatus::ARCHIVED);
    expect($toArchive->fresh()->archived_at)->not->toBeNull();
});

it('rejects a missing or inactive category in the material form', function () {
    $staff = User::factory()->admin()->create();
    $inactiveCategory = Category::factory()->inactive()->create();
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->call('openCreate')
        ->set('form.title', 'Material sem categoria ativa')
        ->set('form.category_id', (string) $inactiveCategory->getKey())
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Pedagógica')
        ->call('save')
        ->assertHasErrors('form.category_id');

    Livewire::test(MaterialManager::class)
        ->call('openCreate')
        ->set('form.title', 'Material sem categoria existente')
        ->set('form.category_id', '999999')
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Pedagógica')
        ->call('save')
        ->assertHasErrors('form.category_id');

    $this->assertDatabaseMissing('materials', ['title' => 'Material sem categoria ativa']);
    $this->assertDatabaseMissing('materials', ['title' => 'Material sem categoria existente']);
});

it('resolves materials through their category relationship', function () {
    $category = Category::factory()->create();
    $material = Material::factory()->for($category)->create();

    expect($category->materials()->whereKey($material->getKey())->exists())->toBeTrue();
});

it('allows teachers to view and download only published materials', function (MaterialStatus $status, bool $canAccess) {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->make(['status' => $status]);

    expect(Gate::forUser($teacher)->allows('view', $material))->toBe($canAccess)
        ->and(Gate::forUser($teacher)->allows('download', $material))->toBe($canAccess);
})->with([
    'draft' => [MaterialStatus::DRAFT, false],
    'published' => [MaterialStatus::PUBLISHED, true],
    'archived' => [MaterialStatus::ARCHIVED, false],
]);

it('allows administrators to manage materials created by any user', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->staffInSchool()->create();
    $material = Material::factory()->make(['created_by' => $creator->getKey()]);

    expect(Gate::forUser($admin)->allows('create', Material::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $material))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('archive', $material))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('publish', $material))->toBeTrue();
});

it('allows management to create materials and manage only materials it created', function () {
    $staff = User::factory()->staffInSchool()->create();
    $otherCreator = User::factory()->admin()->create();
    $ownMaterial = Material::factory()->make(['created_by' => $staff->getKey()]);
    $otherMaterial = Material::factory()->make(['created_by' => $otherCreator->getKey()]);

    expect(Gate::forUser($staff)->allows('create', Material::class))->toBeTrue()
        ->and(Gate::forUser($staff)->allows('update', $ownMaterial))->toBeTrue()
        ->and(Gate::forUser($staff)->allows('archive', $ownMaterial))->toBeTrue()
        ->and(Gate::forUser($staff)->allows('publish', $ownMaterial))->toBeTrue()
        ->and(Gate::forUser($staff)->allows('update', $otherMaterial))->toBeFalse()
        ->and(Gate::forUser($staff)->allows('archive', $otherMaterial))->toBeFalse()
        ->and(Gate::forUser($staff)->allows('publish', $otherMaterial))->toBeFalse();
});

it('forbids teachers from creating or managing materials', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->make();

    expect(Gate::forUser($teacher)->allows('create', Material::class))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('update', $material))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('archive', $material))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('publish', $material))->toBeFalse();
});
