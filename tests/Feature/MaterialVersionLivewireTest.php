<?php

use App\Livewire\Materials\MaterialDetail;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

it('prevents clients from changing the material detail target id', function () {
    $staff = User::factory()->staff()->create();
    $material = Material::factory()->create();
    $otherMaterial = Material::factory()->create();
    $this->actingAs($staff);

    $component = Livewire::test(MaterialDetail::class, ['material' => $material])
        ->assertSet('materialId', $material->getKey());

    expect(fn () => $component->set('materialId', $otherMaterial->getKey()))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

it('allows staff to upload sequential versions and updates the current version', function () {
    Storage::fake('local');
    $material = Material::factory()->create();
    $staff = User::factory()->staff()->create();
    $this->actingAs($staff);

    $component = Livewire::test(MaterialDetail::class, ['material' => $material])
        ->set('versionForm.file', UploadedFile::fake()->create('apostila-1.pdf', 100, 'application/pdf'))
        ->set('versionForm.change_note', 'Primeira versão')
        ->call('saveVersion')
        ->assertHasNoErrors()
        ->assertSet('versionForm.file', null);

    $firstVersion = MaterialVersion::query()->where('material_id', $material->getKey())->firstOrFail();
    expect($firstVersion->version_number)->toBe(1);
    expect(Storage::disk('local')->exists($firstVersion->file_path))->toBeTrue();

    $component
        ->set('versionForm.file', UploadedFile::fake()->create('apostila-2.pdf', 120, 'application/pdf'))
        ->set('versionForm.change_note', 'Segunda versão')
        ->call('saveVersion')
        ->assertHasNoErrors()
        ->assertSet('versionForm.file', null);

    $secondVersion = MaterialVersion::query()
        ->where('material_id', $material->getKey())
        ->where('version_number', 2)
        ->firstOrFail();

    expect(Storage::disk('local')->exists($firstVersion->file_path))->toBeTrue();
    expect(Storage::disk('local')->exists($secondVersion->file_path))->toBeTrue();
    $this->assertDatabaseHas('materials', [
        'id' => $material->getKey(),
        'current_version_id' => $secondVersion->getKey(),
    ]);
});

it('forbids teachers from uploading a version to a published material', function () {
    $material = Material::factory()->published()->create();
    $this->actingAs(User::factory()->teacher()->create());

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->call('saveVersion')
        ->assertForbidden();
});

it('rejects invalid version files and clears the temporary upload', function () {
    Storage::fake('local');
    $material = Material::factory()->create();
    $this->actingAs(User::factory()->staff()->create());

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->set('versionForm.file', UploadedFile::fake()->create('script.txt', 10, 'text/plain'))
        ->call('saveVersion')
        ->assertHasErrors('versionForm.file')
        ->assertSet('versionForm.file', null);

    $this->assertDatabaseMissing('material_versions', ['material_id' => $material->getKey()]);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('uses the material upload mimes and maximum on temporary Livewire uploads', function () {
    $rules = config('livewire.temporary_file_upload.rules');

    expect($rules)
        ->toContain('mimes:'.implode(',', config('materials.upload.allowed_mimes')))
        ->toContain('max:'.config('materials.upload.max_size_kilobytes'));
});

it('shows management links only to admins and staff and library to teachers', function () {
    $staff = User::factory()->staff()->create();
    $this->actingAs($staff)
        ->get('/painel/materiais')
        ->assertSee('Usuários')
        ->assertSee('Categorias')
        ->assertSee('Materiais')
        ->assertDontSee(route('painel.library'), false);

    $teacher = User::factory()->teacher()->create();
    $this->actingAs($teacher)
        ->get('/painel/biblioteca')
        ->assertSee('Biblioteca')
        ->assertSee(route('painel.library'), false)
        ->assertSee(route('painel.library.categories'), false)
        ->assertDontSee('Usuários')
        ->assertDontSee(route('painel.materials'), false)
        ->assertDontSee(route('painel.categories'), false);
});
