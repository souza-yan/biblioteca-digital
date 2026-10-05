<?php

use App\Enums\MaterialStatus;
use App\Livewire\Materials\MaterialDetail;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('allows a teacher to download a published material and records its current version', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create();
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, 'conteúdo do material');

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('downloads.show', $material))
        ->assertOk()
        ->assertDownload($version->original_name);

    $this->assertDatabaseHas('downloads', [
        'user_id' => auth()->id(),
        'material_id' => $material->getKey(),
        'material_version_id' => $version->getKey(),
    ]);
});

it('records every repeated download', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create();
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, 'conteúdo');
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)->get(route('downloads.show', $material))->assertDownload();
    $this->actingAs($teacher)->get(route('downloads.show', $material))->assertDownload();

    $this->assertDatabaseCount('downloads', 2);
});

it('forbids teachers from downloading draft or archived materials without recording them', function (MaterialStatus $status) {
    $material = Material::factory()->create(['status' => $status]);

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('downloads.show', $material))
        ->assertForbidden();

    $this->assertDatabaseCount('downloads', 0);
})->with([
    'draft' => MaterialStatus::DRAFT,
    'archived' => MaterialStatus::ARCHIVED,
]);

it('returns 404 without recording a download when the file is missing', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create();
    $material->update(['current_version_id' => $version->getKey()]);

    $this->actingAs(User::factory()->teacher()->create())
        ->getJson(route('downloads.show', $material))
        ->assertNotFound()
        ->assertJsonPath('message', 'Arquivo não encontrado.');

    $this->assertDatabaseCount('downloads', 0);
});

it('returns 404 without recording a download when no current version exists', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();

    $this->actingAs(User::factory()->teacher()->create())
        ->getJson(route('downloads.show', $material))
        ->assertNotFound()
        ->assertJsonPath('message', 'Arquivo não encontrado.');

    $this->assertDatabaseCount('downloads', 0);
});

it('returns 404 when a requested version belongs to another material', function () {
    Storage::fake('local');
    $material = Material::factory()->create();
    $otherMaterial = Material::factory()->create();
    $version = MaterialVersion::factory()->for($otherMaterial)->create();
    Storage::disk('local')->put($version->file_path, 'conteúdo');

    $this->actingAs(User::factory()->staff()->create())
        ->get(route('downloads.version', [$material, $version]))
        ->assertNotFound();

    $this->assertDatabaseCount('downloads', 0);
});

it('forbids teachers from downloading historical versions', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $oldVersion = MaterialVersion::factory()->for($material)->create(['version_number' => 1]);
    $currentVersion = MaterialVersion::factory()->for($material)->create(['version_number' => 2]);
    $material->update(['current_version_id' => $currentVersion->getKey()]);

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('downloads.version', [$material, $oldVersion]))
        ->assertForbidden();

    $this->assertDatabaseCount('downloads', 0);
});

it('allows staff to download historical versions of archived materials', function () {
    Storage::fake('local');
    $material = Material::factory()->archived()->create();
    $oldVersion = MaterialVersion::factory()->for($material)->create(['version_number' => 1]);
    MaterialVersion::factory()->for($material)->create(['version_number' => 2]);
    Storage::disk('local')->put($oldVersion->file_path, 'versão anterior');

    $this->actingAs(User::factory()->staff()->create())
        ->get(route('downloads.version', [$material, $oldVersion]))
        ->assertOk()
        ->assertDownload($oldVersion->original_name);

    $this->assertDatabaseHas('downloads', [
        'material_id' => $material->getKey(),
        'material_version_id' => $oldVersion->getKey(),
    ]);
});

it('shows historical download links to staff in the material version history', function () {
    $material = Material::factory()->create();
    $version = MaterialVersion::factory()->for($material)->create();

    $this->actingAs(User::factory()->staff()->create());

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->assertSee(route('downloads.version', [$material, $version]), false);
});

it('does not allow inactive or unauthenticated users to download materials', function () {
    $material = Material::factory()->published()->create();

    $this->get(route('downloads.show', $material))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->inactive()->teacher()->create())
        ->get(route('downloads.show', $material))
        ->assertRedirect(route('login'));

    $this->assertDatabaseCount('downloads', 0);
});
