<?php

use App\Livewire\Materials\MaterialDetail;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('stores an administrator upload privately as the first material version', function () {
    Storage::fake('local');
    $material = Material::factory()->create();
    $staff = User::factory()->admin()->create();
    $this->actingAs($staff);

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->set('versionForm.file', UploadedFile::fake()->create('apostila.pdf', 100, 'application/pdf'))
        ->set('versionForm.change_note', 'Primeira versão.')
        ->call('saveVersion')
        ->assertHasNoErrors();

    $version = MaterialVersion::query()->firstOrFail();

    expect($version->version_number)->toBe(1);
    expect($version->published_by)->toBe($staff->getKey());
    expect(Storage::disk('local')->exists($version->file_path))->toBeTrue();
    $this->assertDatabaseHas('materials', [
        'id' => $material->getKey(),
        'current_version_id' => $version->getKey(),
    ]);
});

it('increments versions and preserves older files when an administrator uploads again', function () {
    Storage::fake('local');
    $material = Material::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    $component = Livewire::test(MaterialDetail::class, ['material' => $material])
        ->set('versionForm.file', UploadedFile::fake()->create('apostila-1.pdf', 100, 'application/pdf'))
        ->call('saveVersion')
        ->assertHasNoErrors();
    $firstVersion = MaterialVersion::query()->where('version_number', 1)->firstOrFail();

    $component
        ->set('versionForm.file', UploadedFile::fake()->create('apostila-2.pdf', 120, 'application/pdf'))
        ->call('saveVersion')
        ->assertHasNoErrors();
    $secondVersion = MaterialVersion::query()->where('version_number', 2)->firstOrFail();

    expect(Storage::disk('local')->exists($firstVersion->file_path))->toBeTrue();
    expect(Storage::disk('local')->exists($secondVersion->file_path))->toBeTrue();
    $this->assertDatabaseHas('materials', [
        'id' => $material->getKey(),
        'current_version_id' => $secondVersion->getKey(),
    ]);
});

it('shows version history to administrators but not teachers', function (string $role) {
    $material = Material::factory()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'original_name' => 'versao-privada.pdf',
    ]);
    $actor = $role === 'admin'
        ? User::factory()->admin()->create()
        : User::factory()->admin()->create();
    $this->actingAs($actor);

    $this->get(route('painel.materials.show', $material))
        ->assertOk()
        ->assertSee('Versões')
        ->assertSee($version->original_name)
        ->assertDontSee(route('downloads.version', [$material, $version]), false)
        ->assertDontSee($version->file_path);
})->with(['admin']);

it('does not expose version history or permit uploads to teachers', function () {
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'original_name' => 'versao-privada.pdf',
    ]);
    $this->actingAs(User::factory()->teacher()->create());

    $this->get(route('painel.materials.show', $material))->assertForbidden();
    $this->get(route('painel.library.show', $material))
        ->assertOk()
        ->assertDontSee('Versões')
        ->assertDontSee($version->original_name);

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->call('saveVersion')
        ->assertForbidden();
});

it('rejects invalid version files and does not store them', function () {
    Storage::fake('local');
    $material = Material::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->set('versionForm.file', UploadedFile::fake()->create('script.txt', 10, 'text/plain'))
        ->call('saveVersion')
        ->assertHasErrors('versionForm.file');

    $this->assertDatabaseMissing('material_versions', ['material_id' => $material->getKey()]);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('rejects version files above the configured upload maximum', function () {
    Storage::fake('local');
    config()->set('materials.upload.max_size_kilobytes', 1);
    $material = Material::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->set('versionForm.file', UploadedFile::fake()->create('apostila.pdf', 2, 'application/pdf'))
        ->call('saveVersion')
        ->assertHasErrors('versionForm.file');

    $this->assertDatabaseMissing('material_versions', ['material_id' => $material->getKey()]);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('allows administrators to list and create versions for any material', function () {
    $admin = User::factory()->admin()->create();
    $material = Material::factory()->make();

    expect(Gate::forUser($admin)->allows('viewAny', [MaterialVersion::class, $material]))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', [MaterialVersion::class, $material]))->toBeTrue();
});

it('allows management to list global versions but create versions only for its own materials', function () {
    $staff = User::factory()->staffInSchool()->create();
    $otherCreator = User::factory()->admin()->create();
    $ownMaterial = Material::factory()->make(['created_by' => $staff->getKey()]);
    $otherMaterial = Material::factory()->make(['created_by' => $otherCreator->getKey()]);

    expect(Gate::forUser($staff)->allows('viewAny', [MaterialVersion::class, $otherMaterial]))->toBeTrue()
        ->and(Gate::forUser($staff)->allows('create', [MaterialVersion::class, $ownMaterial]))->toBeTrue()
        ->and(Gate::forUser($staff)->allows('create', [MaterialVersion::class, $otherMaterial]))->toBeFalse();
});

it('forbids teachers from listing or creating material versions', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->make();

    expect(Gate::forUser($teacher)->allows('viewAny', [MaterialVersion::class, $material]))->toBeFalse()
        ->and(Gate::forUser($teacher)->allows('create', [MaterialVersion::class, $material]))->toBeFalse();
});

it('forbids management from uploading a version to another user material', function () {
    Storage::fake('local');
    $staff = User::factory()->staffInSchool()->create();
    $otherCreator = User::factory()->admin()->create();
    $material = Material::factory()->published()->create(['created_by' => $otherCreator->getKey()]);

    Livewire::actingAs($staff)
        ->test(MaterialDetail::class, ['material' => $material])
        ->set('versionForm.file', UploadedFile::fake()->create('apostila.pdf', 100, 'application/pdf'))
        ->call('saveVersion')
        ->assertForbidden();

    $this->assertDatabaseMissing('material_versions', [
        'material_id' => $material->getKey(),
    ]);
});
