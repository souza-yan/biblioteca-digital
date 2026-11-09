<?php

use App\Jobs\GenerateMaterialVersionCover;
use App\Livewire\Materials\MaterialDetail;
use App\Models\Material;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('offers one version file picker and blocks saving while the upload is pending', function () {
    $staff = User::factory()->staffInSchool()->create();
    $material = Material::factory()->create(['created_by' => $staff->getKey()]);

    $response = $this->actingAs($staff)->get(route('painel.library.show', $material));

    $response->assertOk();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
    $xpath = new DOMXPath($document);
    $form = $xpath->query('//form[@*[name()="wire:submit"]="saveVersion"]')->item(0);

    expect($form)->not->toBeNull();
    expect($xpath->query('.//input[@type="file"]', $form)->length)->toBe(1);
    $submit = $xpath->query('.//button[@type="submit"]', $form)->item(0);
    expect($submit->getAttribute('wire:target'))->toBe('saveVersion,versionForm.file');
    expect($submit->getAttribute('class'))->toContain('bg-blue-800');
});

it('lets management upload consecutive versions and resets the completed form', function () {
    Storage::fake('local');
    Queue::fake([GenerateMaterialVersionCover::class]);
    $staff = User::factory()->staffInSchool()->create();
    $material = Material::factory()->create(['created_by' => $staff->getKey()]);

    $component = Livewire::actingAs($staff)->test(MaterialDetail::class, ['material' => $material]);
    foreach (['Primeira versão', 'Segunda versão'] as $index => $note) {
        $component->set('versionForm.file', UploadedFile::fake()->create('versao-'.($index + 1).'.pdf', 100, 'application/pdf'))
            ->set('versionForm.change_note', $note)
            ->call('saveVersion')->assertHasNoErrors()
            ->assertSet('versionForm.file', null)->assertSet('versionForm.change_note', '');
    }

    $versions = $material->versions()->orderBy('version_number')->get();
    expect($versions)->toHaveCount(2);
    foreach ($versions as $index => $version) {
        expect($version->version_number)->toBe($index + 1);
        expect($version->published_by)->toBe($staff->getKey());
        Storage::disk('local')->assertExists($version->file_path);
    }
    $this->assertDatabaseHas('materials', ['id' => $material->getKey(), 'current_version_id' => $versions->last()->getKey()]);
    Queue::assertPushed(GenerateMaterialVersionCover::class, 2);
});

it('shows management the missing file error without creating a version', function () {
    Storage::fake('local');
    $staff = User::factory()->staffInSchool()->create();
    $material = Material::factory()->create(['created_by' => $staff->getKey()]);

    Livewire::actingAs($staff)->test(MaterialDetail::class, ['material' => $material])
        ->call('saveVersion')->assertHasErrors(['versionForm.file' => 'required'])
        ->assertSee('O arquivo da versão é obrigatório.');

    $this->assertDatabaseMissing('material_versions', ['material_id' => $material->getKey()]);
    Storage::disk('local')->assertDirectoryEmpty('materials');
});
