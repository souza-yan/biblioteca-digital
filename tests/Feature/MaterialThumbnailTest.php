<?php

use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('serves the current supported image without registering a material preview', function () {
    Storage::fake('local');
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'mime_type' => 'image/png',
        'original_name' => 'projeto.png',
    ]);
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, 'image bytes');

    $this->actingAs($teacher)
        ->get(route('thumbnails.show', $material))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeaderContains('Content-Disposition', 'inline')
        ->assertHeaderContains('Cache-Control', 'private')
        ->assertHeaderContains('Cache-Control', 'no-store')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    expect(ActivityLog::query()->where('action', ActivityAction::MATERIAL_PREVIEWED->value)->count())->toBe(0);
});

it('forbids teachers from loading a thumbnail for a non-public material', function (MaterialStatus $status) {
    Storage::fake('local');
    $material = Material::factory()->create(['status' => $status]);
    $version = MaterialVersion::factory()->for($material)->create(['mime_type' => 'image/jpeg']);
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, 'image bytes');

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('thumbnails.show', $material))
        ->assertForbidden();

    expect(ActivityLog::query()->count())->toBe(0);
})->with([
    'draft' => MaterialStatus::DRAFT,
    'archived' => MaterialStatus::ARCHIVED,
]);

it('returns 404 for a current file that is not an image', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create(['mime_type' => 'application/zip']);
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, 'archive bytes');

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('thumbnails.show', $material))
        ->assertNotFound();
});
