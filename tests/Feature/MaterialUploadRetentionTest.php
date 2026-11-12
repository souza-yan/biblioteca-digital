<?php

use App\Jobs\GenerateMaterialVersionCover;
use App\Livewire\Categories\CategoryLibrary;
use App\Livewire\Materials\MaterialManager;
use App\Models\Category;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

dataset('material creation upload entry points', [
    'admin management' => [MaterialManager::class, 'openCreate', 'form', 'save'],
    'staff library' => [CategoryLibrary::class, 'openMaterialCreate', 'materialForm', 'saveMaterial'],
]);

it('retains a valid upload after metadata validation fails and saves without another selection', function (string $componentClass, string $openAction, string $form, string $saveAction) {
    Storage::fake('local');
    Queue::fake([GenerateMaterialVersionCover::class]);
    $user = $componentClass === MaterialManager::class ? User::factory()->admin()->create() : User::factory()->staffInSchool()->create();
    $category = Category::factory()->create();
    $component = Livewire::actingAs($user)->test($componentClass)
        ->call($openAction)
        ->set($form.'.title', str_repeat('a', 256))
        ->set($form.'.category_id', (string) $category->getKey())
        ->set($form.'.author', 'Equipe escolar')
        ->set($form.'.file', UploadedFile::fake()->create('arquivo.pdf', 20, 'application/pdf'))
        ->set($form.'.change_note', 'Arquivo enviado uma única vez');
    $uploadedFile = $component->get($form.'.file');

    $component->call($saveAction)->assertHasErrors([$form.'.title' => 'max'])
        ->assertSet($form.'.file', fn ($file): bool => $file instanceof TemporaryUploadedFile && $file->getClientOriginalName() === 'arquivo.pdf');

    expect($uploadedFile->exists())->toBeTrue();
    $this->assertDatabaseCount('materials', 0);
    $component->set($form.'.title', 'Material corrigido')->call($saveAction)
        ->assertHasNoErrors()->assertSet($form.'.file', null);

    $version = MaterialVersion::query()->firstOrFail();
    $this->assertDatabaseHas('materials', ['title' => 'Material corrigido', 'created_by' => $user->getKey()]);
    $this->assertDatabaseCount('material_versions', 1);
    Storage::disk('local')->assertExists($version->file_path);
    expect($uploadedFile->exists())->toBeFalse();
})->with('material creation upload entry points');

it('still rejects and discards an invalid file instead of retaining it', function (string $componentClass, string $openAction, string $form, string $saveAction) {
    Storage::fake('local');
    config()->set('livewire.temporary_file_upload.rules', ['file']);
    $user = $componentClass === MaterialManager::class ? User::factory()->admin()->create() : User::factory()->staffInSchool()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($user)->test($componentClass)->call($openAction)
        ->set($form.'.title', 'Arquivo inválido')->set($form.'.author', 'Equipe escolar')
        ->set($form.'.category_id', (string) $category->getKey())
        ->set($form.'.file', UploadedFile::fake()->create('script.txt', 10, 'text/plain'))
        ->call($saveAction)->assertHasErrors([$form.'.file' => 'mimes'])->assertSet($form.'.file', null);

    $this->assertDatabaseCount('materials', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
})->with('material creation upload entry points');

it('waits for uploads and leaves required file validation to the backend', function (string $componentClass, string $openAction, string $form, string $saveAction) {
    $user = $componentClass === MaterialManager::class ? User::factory()->admin()->create() : User::factory()->staffInSchool()->create();
    $component = Livewire::actingAs($user)->test($componentClass)->call($openAction);
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$component->html());
    $xpath = new DOMXPath($document);
    $input = $xpath->query('//input[@type="file"]')->item(0);
    $submit = $xpath->query('//button[@type="submit" and @*[name()="wire:target"]="'.$saveAction.','.$form.'.file"]')->item(0);

    expect($input)->not->toBeNull();
    expect($input->hasAttribute('required'))->toBeFalse();
    expect($input->getAttribute('aria-required'))->toBe('true');
    expect($submit)->not->toBeNull();
    expect($submit->hasAttribute('wire:loading.attr'))->toBeTrue();

    $component->call($saveAction)->assertHasErrors([$form.'.file' => 'required']);
    $this->assertDatabaseCount('materials', 0);
})->with('material creation upload entry points');
