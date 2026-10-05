<?php

use App\Livewire\Materials\MaterialDetail;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('stores a staff upload privately as the first material version', function () {
    Storage::fake('local');
    $material = Material::factory()->create();
    $staff = User::factory()->staff()->create();
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

it('increments versions and preserves older files when staff uploads again', function () {
    Storage::fake('local');
    $material = Material::factory()->create();
    $this->actingAs(User::factory()->staff()->create());

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

it('shows version history to staff and administrators but not teachers', function (string $role) {
    $material = Material::factory()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'original_name' => 'versao-privada.pdf',
    ]);
    $actor = $role === 'admin'
        ? User::factory()->admin()->create()
        : User::factory()->staff()->create();
    $this->actingAs($actor);

    $this->get(route('painel.materials.show', $material))
        ->assertOk()
        ->assertSee('Versões')
        ->assertSee($version->original_name)
        ->assertSee(route('downloads.version', [$material, $version]), false)
        ->assertDontSee($version->file_path);
})->with(['admin', 'staff']);

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
    $this->actingAs(User::factory()->staff()->create());

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
    $this->actingAs(User::factory()->staff()->create());

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->set('versionForm.file', UploadedFile::fake()->create('apostila.pdf', 2, 'application/pdf'))
        ->call('saveVersion')
        ->assertHasErrors('versionForm.file');

    $this->assertDatabaseMissing('material_versions', ['material_id' => $material->getKey()]);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('allows only admins and staff to list or create material versions', function (string $role, bool $allowed) {
    $user = match ($role) {
        'admin' => User::factory()->admin()->create(),
        'staff' => User::factory()->staff()->create(),
        default => User::factory()->teacher()->create(),
    };
    $material = Material::factory()->make();

    expect(Gate::forUser($user)->allows('viewAny', [MaterialVersion::class, $material]))->toBe($allowed)
        ->and(Gate::forUser($user)->allows('create', [MaterialVersion::class, $material]))->toBe($allowed);
})->with([
    'admin' => ['admin', true],
    'staff' => ['staff', true],
    'teacher' => ['teacher', false],
]);
