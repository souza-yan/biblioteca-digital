<?php

use App\Enums\ActivityAction;
use App\Enums\MaterialStatus;
use App\Livewire\Materials\MaterialDetail;
use App\Models\ActivityLog;
use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('serves a published PDF preview to teachers without recording a download', function () {
    Storage::fake('local');
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'mime_type' => 'application/pdf',
        'original_name' => 'apostila.pdf',
        'size' => 17,
    ]);
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, '%PDF-1.4 conteúdo');

    $response = $this->actingAs($teacher)->get(route('previews.show', $material));

    $response->assertOk()
        ->assertHeader('Content-Disposition', 'inline; filename=apostila.pdf')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Security-Policy', "default-src 'none'; img-src 'self' data:; media-src 'self'; style-src 'unsafe-inline'; sandbox")
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');

    expect(Download::query()->count())->toBe(0);
    expect(json_encode($response->headers->all()))->not->toContain($version->file_path);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $teacher->getKey(),
        'material_id' => $material->getKey(),
        'action' => ActivityAction::MATERIAL_PREVIEWED->value,
    ]);
});

it('forbids teachers from previewing draft or archived materials without logging the attempt', function (MaterialStatus $status) {
    Storage::fake('local');
    $material = Material::factory()->create(['status' => $status]);
    $version = MaterialVersion::factory()->for($material)->create();
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, 'conteúdo');

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('previews.show', $material))
        ->assertForbidden();

    expect(Download::query()->count())->toBe(0)
        ->and(ActivityLog::query()->count())->toBe(0);
})->with([
    'draft' => MaterialStatus::DRAFT,
    'archived' => MaterialStatus::ARCHIVED,
]);

it('returns 404 for MIME types that cannot be previewed', function (string $mimeType) {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create(['mime_type' => $mimeType]);
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, 'conteúdo');

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('previews.show', $material))
        ->assertNotFound();

    expect(Download::query()->count())->toBe(0)
        ->and(ActivityLog::query()->count())->toBe(0);
})->with([
    'SVG' => 'image/svg+xml',
    'HTML' => 'text/html',
    'Word document' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'unknown' => 'application/x-unknown',
]);

it('returns 404 for a missing current file without recording a preview', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create(['mime_type' => 'application/pdf']);
    $material->update(['current_version_id' => $version->getKey()]);

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('previews.show', $material))
        ->assertNotFound();

    expect(Download::query()->count())->toBe(0)
        ->and(ActivityLog::query()->count())->toBe(0);
});

it('returns 404 when a historical version belongs to another material', function () {
    Storage::fake('local');
    $material = Material::factory()->create();
    $otherMaterial = Material::factory()->create();
    $version = MaterialVersion::factory()->for($otherMaterial)->create();
    Storage::disk('local')->put($version->file_path, 'conteúdo');

    $this->actingAs(User::factory()->staff()->create())
        ->get(route('previews.version', [$material, $version]))
        ->assertNotFound();

    expect(ActivityLog::query()->count())->toBe(0);
});

it('serves supported image types as inline previews', function (string $mimeType) {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create(['mime_type' => $mimeType]);
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, 'image bytes');

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('previews.show', $material))
        ->assertOk()
        ->assertHeader('Content-Type', $mimeType)
        ->assertHeaderContains('Content-Disposition', 'inline');
})->with([
    'PNG' => 'image/png',
    'JPEG' => 'image/jpeg',
    'WebP' => 'image/webp',
    'GIF' => 'image/gif',
]);

it('forbids teachers from previewing a historical version', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create(['version_number' => 1]);
    $currentVersion = MaterialVersion::factory()->for($material)->create(['version_number' => 2]);
    $material->update(['current_version_id' => $currentVersion->getKey()]);
    Storage::disk('local')->put($version->file_path, 'versão anterior');

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('previews.version', [$material, $version]))
        ->assertForbidden();

    expect(ActivityLog::query()->count())->toBe(0);
});

it('supports byte range requests for video and audio previews', function (string $mimeType) {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create(['mime_type' => $mimeType]);
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, '0123456789');

    $this->actingAs(User::factory()->teacher()->create())
        ->withHeader('Range', 'bytes=0-3')
        ->get(route('previews.show', $material))
        ->assertStatus(206)
        ->assertHeader('Content-Range', 'bytes 0-3/10')
        ->assertHeaderContains('Content-Disposition', 'inline');
})->with([
    'MP4 video' => 'video/mp4',
    'WebM video' => 'video/webm',
    'MP3 audio' => 'audio/mpeg',
]);

it('renders escaped text content in a pre element', function () {
    Storage::fake('local');
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'mime_type' => 'text/plain',
        'original_name' => 'anotacoes.txt',
    ]);
    $material->update(['current_version_id' => $version->getKey()]);
    Storage::disk('local')->put($version->file_path, '<script>alert("x")</script>');

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('previews.show', $material))
        ->assertOk()
        ->assertSee('<pre>', false)
        ->assertSee('&lt;script&gt;', false)
        ->assertDontSee('<script>alert("x")</script>', false);
});

it('shows the preview fallback and download link for unsupported files', function () {
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'mime_type' => 'application/zip',
        'original_name' => 'recursos.zip',
        'size' => 2048,
    ]);
    $material->update(['current_version_id' => $version->getKey()]);

    Livewire::actingAs(User::factory()->teacher()->create())
        ->test(MaterialDetail::class, ['material' => $material])
        ->assertSee('Prévia não disponível para este tipo')
        ->assertSee('application/zip')
        ->assertSee('recursos.zip')
        ->assertSee('2.0 KB')
        ->assertSee(route('downloads.show', $material), false);
});

it('shows a preview control and download link for each historical version to staff', function () {
    $material = Material::factory()->create();
    $version = MaterialVersion::factory()->for($material)->create([
        'mime_type' => 'application/pdf',
        'original_name' => 'versao-anterior.pdf',
    ]);

    Livewire::actingAs(User::factory()->staff()->create())
        ->test(MaterialDetail::class, ['material' => $material])
        ->assertSee('Ver prévia')
        ->assertSee('versao-anterior.pdf')
        ->assertSee('application/pdf')
        ->assertSee(route('previews.version', [$material, $version]), false)
        ->assertSee(route('downloads.version', [$material, $version]), false);
});

it('shows the correct preview element for supported image and media types', function (string $mimeType, string $element) {
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create(['mime_type' => $mimeType]);
    $material->update(['current_version_id' => $version->getKey()]);

    Livewire::actingAs(User::factory()->teacher()->create())
        ->test(MaterialDetail::class, ['material' => $material])
        ->assertSee($element, false)
        ->assertSee(route('previews.show', $material), false);
})->with([
    'PDF document' => ['application/pdf', '<iframe'],
    'PNG image' => ['image/png', '<img'],
    'JPEG image' => ['image/jpeg', '<img'],
    'WebP image' => ['image/webp', '<img'],
    'GIF image' => ['image/gif', '<img'],
    'Plain text' => ['text/plain', '<iframe'],
    'MP4 video' => ['video/mp4', '<video'],
    'WebM video' => ['video/webm', '<video'],
    'MP3 audio' => ['audio/mpeg', '<audio'],
]);

it('allows staff to preview historical versions and records the preview action', function () {
    Storage::fake('local');
    $staff = User::factory()->staff()->create();
    $material = Material::factory()->archived()->create();
    $version = MaterialVersion::factory()->for($material)->create(['mime_type' => 'application/pdf']);
    Storage::disk('local')->put($version->file_path, '%PDF historical');

    $this->actingAs($staff)
        ->get(route('previews.version', [$material, $version]))
        ->assertOk()
        ->assertHeader('Content-Disposition', 'inline; filename=material.pdf');

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $staff->getKey(),
        'material_id' => $material->getKey(),
        'action' => ActivityAction::MATERIAL_PREVIEWED->value,
    ]);
    expect(Download::query()->count())->toBe(0);
});

it('redirects unauthenticated and inactive users away from previews', function () {
    $material = Material::factory()->published()->create();

    $this->get(route('previews.show', $material))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->inactive()->teacher()->create())
        ->get(route('previews.show', $material))
        ->assertRedirect(route('login'));
});
