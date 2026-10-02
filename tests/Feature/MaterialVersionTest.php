<?php

use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

it('stores a staff upload privately as the first material version', function () {
    Storage::fake('local');
    $material = Material::factory()->create();
    $staff = User::factory()->staff()->create();
    $otherUser = User::factory()->admin()->create();

    $response = $this->actingAs($staff)
        ->postJson("/materials/{$material->id}/versions", [
            'file' => UploadedFile::fake()->create('apostila.pdf', 100, 'application/pdf'),
            'change_note' => 'Primeira versão.',
            'published_by' => $otherUser->getKey(),
        ])
        ->assertCreated()
        ->assertJsonPath('version_number', 1)
        ->assertJsonPath('original_name', 'apostila.pdf')
        ->assertJsonPath('published_by', $staff->getKey());

    $version = MaterialVersion::query()->firstOrFail();

    expect($version->file_path)->toStartWith("materials/{$material->id}/versions/");
    expect(Storage::disk('local')->exists($version->file_path))->toBeTrue();
    $this->assertDatabaseHas('material_versions', [
        'id' => $version->getKey(),
        'material_id' => $material->getKey(),
        'version_number' => 1,
        'published_by' => $staff->getKey(),
    ]);
    $this->assertDatabaseHas('materials', [
        'id' => $material->getKey(),
        'current_version_id' => $version->getKey(),
    ]);
});

it('increments versions and preserves older files when staff uploads again', function () {
    Storage::fake('local');
    $material = Material::factory()->create();
    $staff = User::factory()->staff()->create();

    $firstResponse = $this->actingAs($staff)
        ->postJson("/materials/{$material->id}/versions", [
            'file' => UploadedFile::fake()->create('apostila-1.pdf', 100, 'application/pdf'),
        ])
        ->assertCreated()
        ->assertJsonPath('version_number', 1);
    $firstPath = (string) $firstResponse->json('file_path');

    $secondResponse = $this->postJson("/materials/{$material->id}/versions", [
        'file' => UploadedFile::fake()->create('apostila-2.pdf', 120, 'application/pdf'),
    ])
        ->assertCreated()
        ->assertJsonPath('version_number', 2);
    $secondPath = (string) $secondResponse->json('file_path');
    $currentVersionId = $secondResponse->json('id');

    expect(Storage::disk('local')->exists($firstPath))->toBeTrue();
    expect(Storage::disk('local')->exists($secondPath))->toBeTrue();
    $this->assertDatabaseHas('material_versions', [
        'material_id' => $material->getKey(),
        'version_number' => 1,
        'file_path' => $firstPath,
    ]);
    $this->assertDatabaseHas('material_versions', [
        'id' => $currentVersionId,
        'material_id' => $material->getKey(),
        'version_number' => 2,
        'file_path' => $secondPath,
    ]);
    $this->assertDatabaseHas('materials', [
        'id' => $material->getKey(),
        'current_version_id' => $currentVersionId,
    ]);

    $this->getJson("/materials/{$material->id}/versions")
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.version_number', 2);
});

it('returns 403 when a teacher tries to access material versions', function () {
    $material = Material::factory()->create();

    $this->actingAs(User::factory()->teacher()->create())
        ->getJson("/materials/{$material->id}/versions")
        ->assertForbidden();
});

it('returns 403 when a teacher tries to upload a material version', function () {
    $material = Material::factory()->create();

    $this->actingAs(User::factory()->teacher()->create())
        ->postJson("/materials/{$material->id}/versions", [])
        ->assertForbidden();
});

it('returns 422 and does not store a file with a disallowed format', function () {
    Storage::fake('local');
    $material = Material::factory()->create();

    $this->actingAs(User::factory()->staff()->create())
        ->postJson("/materials/{$material->id}/versions", [
            'file' => UploadedFile::fake()->create('script.txt', 10, 'text/plain'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    expect(Storage::disk('local')->allFiles())->toBe([]);
    $this->assertDatabaseMissing('material_versions', ['material_id' => $material->getKey()]);
});

it('uses the configured maximum upload size', function () {
    Storage::fake('local');
    config()->set('materials.upload.max_size_kilobytes', 1);
    $material = Material::factory()->create();

    $this->actingAs(User::factory()->staff()->create())
        ->postJson("/materials/{$material->id}/versions", [
            'file' => UploadedFile::fake()->create('apostila.pdf', 2, 'application/pdf'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('only admins and staff can list or create material versions', function (string $role, bool $allowed) {
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
