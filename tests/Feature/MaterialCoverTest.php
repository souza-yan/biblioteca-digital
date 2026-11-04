<?php

use App\Actions\MaterialVersion\CreateMaterialVersion;
use App\Actions\MaterialVersion\GeneratePdfCover;
use App\Enums\MaterialStatus;
use App\Jobs\GenerateMaterialVersionCover;
use App\Livewire\Materials\MaterialDetail;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery\MockInterface;
use RuntimeException;

it('generates a private JPEG cover from the first page of a PDF', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'mime_type' => 'application/pdf',
        'file_path' => 'materials/'.$material->getKey().'/versions/cover-source.pdf',
    ]);
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, file_get_contents(base_path('tests/Fixtures/first-page.pdf')));

    app(GeneratePdfCover::class)->handle($version);

    $version->refresh();

    expect($version->cover_path)->toBe("materials/{$material->getKey()}/covers/version-{$version->getKey()}.jpg")
        ->and(Storage::disk('local')->exists($version->cover_path))->toBeTrue()
        ->and(Storage::disk('local')->get($version->cover_path))->toStartWith("\xFF\xD8");
});

it('keeps the PDF version accessible when cover generation fails', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'mime_type' => 'application/pdf',
        'file_path' => 'materials/'.$material->getKey().'/versions/invalid.pdf',
    ]);
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, 'arquivo PDF corrompido');

    expect(fn () => app(GeneratePdfCover::class)->handle($version))->toThrow(RuntimeException::class);

    $version->refresh();

    expect($version->cover_path)->toBeNull()
        ->and(Storage::disk('local')->exists($version->file_path))->toBeTrue();
});

it('does not generate a second cover when the version already has one', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'mime_type' => 'application/pdf',
        'file_path' => 'materials/'.$material->getKey().'/versions/idempotent.pdf',
    ]);
    Storage::disk('local')->put($version->file_path, file_get_contents(base_path('tests/Fixtures/first-page.pdf')));

    app(GeneratePdfCover::class)->handle($version);
    $coverPath = $version->fresh()->cover_path;
    config()->set('materials.pdf_cover.binary', 'pdftoppm-inexistente');

    app(GeneratePdfCover::class)->handle($version->fresh());

    expect($version->fresh()->cover_path)->toBe($coverPath)
        ->and(Storage::disk('local')->exists($coverPath))->toBeTrue();
});

it('skips cover generation while another worker holds the version lock', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'mime_type' => 'application/pdf',
        'file_path' => 'materials/'.$material->getKey().'/versions/locked.pdf',
    ]);
    Storage::disk('local')->put($version->file_path, file_get_contents(base_path('tests/Fixtures/first-page.pdf')));
    $lock = Cache::lock(
        "material-version-cover:{$version->getKey()}",
        (int) config('materials.pdf_cover.lock_seconds'),
    );

    expect($lock->get())->toBeTrue();

    try {
        app(GeneratePdfCover::class)->handle($version);
    } finally {
        $lock->release();
    }

    expect($version->fresh()->cover_path)->toBeNull();
});

it('does not queue cover generation for non-PDF versions', function () {
    Queue::fake([GenerateMaterialVersionCover::class]);
    Storage::fake('local');
    $administrator = User::factory()->admin()->create();
    $material = Material::factory()->create(['created_by' => $administrator->getKey()]);

    app(CreateMaterialVersion::class)->handle(
        $material,
        $administrator,
        UploadedFile::fake()->image('atividade.jpg'),
        null,
    );

    Queue::assertNothingPushed();
});

it('queues a unique cover job for each uploaded PDF version without changing older covers', function () {
    Queue::fake([GenerateMaterialVersionCover::class]);
    Storage::fake('local');
    $administrator = User::factory()->admin()->create();
    $material = Material::factory()->published()->create(['created_by' => $administrator->getKey()]);
    $firstVersion = MaterialVersion::factory()->for($material)->create([
        'version_number' => 1,
        'cover_path' => 'materials/'.$material->getKey().'/covers/version-1.jpg',
    ]);
    $material->update(['current_version_id' => $firstVersion->getKey()]);
    Storage::disk('local')->put($firstVersion->cover_path, "\xFF\xD8\xFF\xD9");

    Livewire::actingAs($administrator)
        ->test(MaterialDetail::class, ['material' => $material])
        ->set('versionForm.file', UploadedFile::fake()->create('segunda-versao.pdf', 20, 'application/pdf'))
        ->call('saveVersion')
        ->assertHasNoErrors();

    $secondVersion = MaterialVersion::query()
        ->where('material_id', $material->getKey())
        ->where('version_number', 2)
        ->firstOrFail();

    expect($firstVersion->fresh()->cover_path)->toBe('materials/'.$material->getKey().'/covers/version-1.jpg')
        ->and(Storage::disk('local')->exists($firstVersion->cover_path))->toBeTrue()
        ->and($secondVersion->cover_path)->toBeNull();

    Queue::assertPushed(
        GenerateMaterialVersionCover::class,
        fn (GenerateMaterialVersionCover $job): bool => $job->materialVersionId === $secondVersion->getKey(),
    );
});

it('serves current covers only to users who can view the material', function () {
    Storage::fake('local');
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'cover_path' => 'materials/'.$material->getKey().'/covers/current.jpg',
    ]);
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->cover_path, "\xFF\xD8\xFF\xD9");

    $response = $this->actingAs($teacher)->get(route('covers.show', $material));

    $response->assertOk()
        ->assertHeader('Content-Type', 'image/jpeg')
        ->assertHeaderContains('Content-Disposition', 'inline')
        ->assertHeaderContains('Cache-Control', 'private')
        ->assertHeaderContains('Cache-Control', 'max-age=3600')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
    expect(json_encode($response->headers->all()))->not->toContain($version->cover_path);
});

it('forbids teachers from accessing draft and historical PDF covers', function () {
    Storage::fake('local');
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->draft()->create();
    $historicalVersion = MaterialVersion::factory()->for($material)->create([
        'version_number' => 1,
        'cover_path' => 'materials/'.$material->getKey().'/covers/old.jpg',
    ]);
    $currentVersion = MaterialVersion::factory()->for($material)->create([
        'version_number' => 2,
        'cover_path' => 'materials/'.$material->getKey().'/covers/current.jpg',
    ]);
    $material->update(['current_version_id' => $currentVersion->getKey()]);
    Storage::disk('local')->put($historicalVersion->cover_path, "\xFF\xD8\xFF\xD9");
    Storage::disk('local')->put($currentVersion->cover_path, "\xFF\xD8\xFF\xD9");

    $this->actingAs($teacher)
        ->get(route('covers.show', $material))
        ->assertForbidden();

    $material->update(['status' => MaterialStatus::PUBLISHED]);

    $this->get(route('covers.version', [$material, $historicalVersion]))
        ->assertForbidden();
});

it('renders a standardized placeholder when a version has no PDF cover', function () {
    $material = Material::factory()->published()->create(['title' => 'Material sem capa']);
    $version = MaterialVersion::factory()->for($material)->create([
        'mime_type' => 'application/pdf',
        'cover_path' => null,
    ]);

    $html = Blade::render(
        '<x-materials.pdf-cover :material="$material" :version="$version" />',
        compact('material', 'version'),
    );

    expect($html)->toContain('Capa indisponível para Material sem capa')
        ->and($html)->toContain('PDF')
        ->and($html)->not->toContain('<img');
});

it('renders an image thumbnail for a current supported image version', function () {
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create(['mime_type' => 'image/png']);
    $material->update(['current_version_id' => $version->getKey()]);

    $html = Blade::render(
        '<x-materials.pdf-cover :material="$material" :version="$version" />',
        compact('material', 'version'),
    );

    expect($html)->toContain(route('thumbnails.show', $material))
        ->and($html)->toContain('<img');
});

it('continues when the asynchronous cover job cannot convert a PDF', function () {
    $version = MaterialVersion::factory()->create(['cover_path' => null]);

    $this->mock(GeneratePdfCover::class, function (MockInterface $mock): void {
        $mock->shouldReceive('handle')->once()->andThrow(new RuntimeException('Poppler indisponível.'));
    });

    app(GenerateMaterialVersionCover::class, ['materialVersionId' => $version->getKey()])
        ->handle(app(GeneratePdfCover::class));

    expect($version->fresh()->cover_path)->toBeNull();
});

it('queues only PDF versions without a cover in the backfill command', function () {
    Queue::fake([GenerateMaterialVersionCover::class]);
    $pdfWithoutCover = MaterialVersion::factory()->create([
        'mime_type' => 'application/pdf',
        'cover_path' => null,
    ]);
    MaterialVersion::factory()->create([
        'mime_type' => 'application/pdf',
        'cover_path' => 'materials/covered.jpg',
    ]);
    MaterialVersion::factory()->create(['mime_type' => 'image/png']);

    $this->artisan('materials:generate-pdf-covers')
        ->expectsOutput('1 versão(ões) PDF foram enfileiradas para gerar capa.')
        ->assertExitCode(0);

    Queue::assertPushed(
        GenerateMaterialVersionCover::class,
        fn (GenerateMaterialVersionCover $job): bool => $job->materialVersionId === $pdfWithoutCover->getKey(),
    );
});
