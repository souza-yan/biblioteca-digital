<?php

use App\Actions\Activity\LogActivity;
use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Livewire\Materials\MaterialManager;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery\MockInterface;

it('creates a draft material with its first private version', function () {
    Storage::fake('local');
    $staff = User::factory()->staff()->create();
    $category = Category::factory()->create();
    $file = UploadedFile::fake()->create('apostila.pdf', 100, 'application/pdf');
    $detectedMime = $file->getMimeType();
    $this->actingAs($staff);

    Livewire::test(MaterialManager::class)
        ->call('openCreate')
        ->assertViewHas('creationStatuses', [MaterialStatus::DRAFT, MaterialStatus::PUBLISHED])
        ->set('form.title', 'Apostila de Robótica')
        ->set('form.description', 'Atividades introdutórias.')
        ->set('form.category_id', (string) $category->getKey())
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Escolar')
        ->set('form.file', $file)
        ->assertSee('apostila.pdf')
        ->assertSee($detectedMime)
        ->set('form.change_note', 'Primeira edição.')
        ->assertSee('Versão 1')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('form.file', null)
        ->assertSet('showForm', false);

    $material = Material::query()->where('title', 'Apostila de Robótica')->firstOrFail();
    $version = MaterialVersion::query()->where('material_id', $material->getKey())->firstOrFail();

    expect($material->status)->toBe(MaterialStatus::DRAFT)
        ->and($material->created_by)->toBe($staff->getKey())
        ->and($material->current_version_id)->toBe($version->getKey())
        ->and($version->version_number)->toBe(1)
        ->and($version->published_by)->toBe($staff->getKey())
        ->and($version->original_name)->toBe('apostila.pdf')
        ->and($version->mime_type)->toBe($detectedMime)
        ->and($version->size)->toBe(102400)
        ->and($version->change_note)->toBe('Primeira edição.')
        ->and(Storage::disk('local')->exists($version->file_path))->toBeTrue();

    $this->assertDatabaseHas('activity_logs', [
        'material_id' => $material->getKey(),
        'action' => ActivityAction::MATERIAL_CREATED->value,
    ]);
    $this->assertDatabaseHas('activity_logs', [
        'material_id' => $material->getKey(),
        'action' => ActivityAction::VERSION_CREATED->value,
    ]);
});

it('creates and publishes a material with its first version when requested', function () {
    Storage::fake('local');
    $staff = User::factory()->staff()->create();
    $category = Category::factory()->create();
    $this->actingAs($staff);

    $component = Livewire::test(MaterialManager::class)
        ->call('openCreate')
        ->set('form.title', 'Material para publicação')
        ->set('form.category_id', (string) $category->getKey())
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Escolar')
        ->set('form.file', UploadedFile::fake()->create('publicacao.pdf', 80, 'application/pdf'))
        ->set('form.initialStatus', MaterialStatus::PUBLISHED->value)
        ->assertSet('form.initialStatus', MaterialStatus::PUBLISHED->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showForm', false);

    $material = Material::query()->where('title', 'Material para publicação')->firstOrFail();
    $version = MaterialVersion::query()->where('material_id', $material->getKey())->firstOrFail();

    expect($material->status)->toBe(MaterialStatus::PUBLISHED)
        ->and($material->published_at)->not->toBeNull()
        ->and($material->created_by)->toBe($staff->getKey())
        ->and($material->current_version_id)->toBe($version->getKey())
        ->and($version->version_number)->toBe(1)
        ->and($version->published_by)->toBe($staff->getKey())
        ->and(Storage::disk('local')->exists($version->file_path))->toBeTrue();

    $this->assertDatabaseHas('activity_logs', [
        'material_id' => $material->getKey(),
        'action' => ActivityAction::MATERIAL_PUBLISHED->value,
    ]);
});

it('requires a file when creating a material and clears the upload field', function () {
    Storage::fake('local');
    $this->actingAs(User::factory()->staff()->create());
    $category = Category::factory()->create();

    Livewire::test(MaterialManager::class)
        ->call('openCreate')
        ->set('form.title', 'Material sem arquivo')
        ->set('form.category_id', (string) $category->getKey())
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Escolar')
        ->call('save')
        ->assertHasErrors('form.file')
        ->assertSet('form.file', null);

    $this->assertDatabaseMissing('materials', ['title' => 'Material sem arquivo']);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('rejects invalid upload formats without creating a material or retaining the upload', function () {
    Storage::fake('local');
    $this->actingAs(User::factory()->staff()->create());
    $category = Category::factory()->create();

    Livewire::test(MaterialManager::class)
        ->call('openCreate')
        ->set('form.title', 'Material com arquivo inválido')
        ->set('form.category_id', (string) $category->getKey())
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Escolar')
        ->set('form.file', UploadedFile::fake()->create('script.txt', 10, 'text/plain'))
        ->call('save')
        ->assertHasErrors('form.file')
        ->assertSet('form.file', null);

    $this->assertDatabaseMissing('materials', ['title' => 'Material com arquivo inválido']);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('rejects a creation upload above the configured maximum size', function () {
    Storage::fake('local');
    config()->set('materials.upload.max_size_kilobytes', 1);
    $this->actingAs(User::factory()->staff()->create());
    $category = Category::factory()->create();

    Livewire::test(MaterialManager::class)
        ->call('openCreate')
        ->set('form.title', 'Material muito grande')
        ->set('form.category_id', (string) $category->getKey())
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Escolar')
        ->set('form.file', UploadedFile::fake()->create('grande.pdf', 2, 'application/pdf'))
        ->call('save')
        ->assertHasErrors('form.file')
        ->assertSet('form.file', null);

    $this->assertDatabaseMissing('materials', ['title' => 'Material muito grande']);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('rolls back the material and removes the saved file when initial version creation fails', function () {
    Storage::fake('local');
    $staff = User::factory()->staff()->create();
    $category = Category::factory()->create();
    $this->actingAs($staff);
    $logActivity = new LogActivity;

    $this->mock(LogActivity::class, function (MockInterface $mock) use ($logActivity): void {
        $mock->shouldReceive('handle')
            ->twice()
            ->andReturnUsing(function (
                User $actor,
                ActivityAction $action,
                string $description,
                ?Material $material = null,
            ) use ($logActivity): ActivityLog {
                $activity = $logActivity->handle($actor, $action, $description, $material);

                if ($action === ActivityAction::VERSION_CREATED) {
                    throw new RuntimeException('Falha simulada ao registrar a versão.');
                }

                return $activity;
            });
    });

    $component = Livewire::test(MaterialManager::class)
        ->call('openCreate')
        ->set('form.title', 'Material cuja versão falhou')
        ->set('form.category_id', (string) $category->getKey())
        ->set('form.type', 'pdf')
        ->set('form.author', 'Equipe Escolar')
        ->set('form.file', UploadedFile::fake()->create('falha.pdf', 100, 'application/pdf'));

    expect(fn () => $component->call('save'))
        ->toThrow(RuntimeException::class, 'Falha simulada ao registrar a versão.');

    $this->assertDatabaseMissing('materials', ['title' => 'Material cuja versão falhou']);
    $this->assertDatabaseMissing('material_versions', ['original_name' => 'falha.pdf']);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('forbids teachers from creating materials with the unified form', function () {
    $this->actingAs(User::factory()->teacher()->create());

    Livewire::test(MaterialManager::class)
        ->assertForbidden();

    $this->assertDatabaseCount('materials', 0);
    $this->assertDatabaseCount('material_versions', 0);
});
