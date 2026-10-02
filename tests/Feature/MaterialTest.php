<?php

use App\Enums\MaterialStatus;
use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('shows teachers only published materials in the index', function () {
    Material::factory()->draft()->create(['title' => 'Material em rascunho']);
    Material::factory()->published()->create(['title' => 'Material publicado']);
    Material::factory()->archived()->create(['title' => 'Material arquivado']);

    $this->actingAs(User::factory()->teacher()->create())
        ->getJson('/materials')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Material publicado')
        ->assertJsonMissing(['title' => 'Material em rascunho'])
        ->assertJsonMissing(['title' => 'Material arquivado']);
});

it('returns 403 when a teacher requests a draft material', function () {
    $material = Material::factory()->draft()->create();

    $this->actingAs(User::factory()->teacher()->create())
        ->getJson("/materials/{$material->id}")
        ->assertForbidden();
});

it('allows staff to create materials using the authenticated user as creator', function () {
    $staff = User::factory()->staff()->create();
    $otherUser = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $this->actingAs($staff)
        ->postJson('/materials', [
            'title' => 'Apostila de Ciências',
            'description' => 'Material introdutório.',
            'category_id' => $category->id,
            'type' => 'pdf',
            'author' => 'Equipe Pedagógica',
            'created_by' => $otherUser->id,
        ])
        ->assertCreated()
        ->assertJsonPath('status', MaterialStatus::DRAFT->value)
        ->assertJsonPath('created_by', $staff->id);

    $this->assertDatabaseHas('materials', [
        'title' => 'Apostila de Ciências',
        'category_id' => $category->id,
        'created_by' => $staff->id,
        'status' => MaterialStatus::DRAFT->value,
    ]);
});

it('returns 403 when a teacher tries to create a material', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->teacher()->create())
        ->postJson('/materials', [
            'title' => 'Apostila',
            'category_id' => $category->id,
            'type' => 'pdf',
            'author' => 'Equipe Pedagógica',
        ])
        ->assertForbidden();
});

it('publishes a material and records published_at', function () {
    $material = Material::factory()->draft()->create();
    $version = MaterialVersion::factory()->for($material)->create();
    $material->update(['current_version_id' => $version->getKey()]);

    $response = $this->actingAs(User::factory()->staff()->create())
        ->patchJson("/materials/{$material->id}/publish")
        ->assertOk()
        ->assertJsonPath('status', MaterialStatus::PUBLISHED->value);

    expect($response->json('published_at'))->not->toBeNull();
    expect($material->fresh()->published_at)->not->toBeNull();
    $this->assertDatabaseHas('materials', [
        'id' => $material->id,
        'status' => MaterialStatus::PUBLISHED->value,
    ]);
});

it('returns 422 when staff publishes a material without a current version', function () {
    $material = Material::factory()->draft()->create();

    $this->actingAs(User::factory()->staff()->create())
        ->patchJson("/materials/{$material->id}/publish")
        ->assertUnprocessable();

    expect($material->fresh()->status)->toBe(MaterialStatus::DRAFT);
});

it('archives a material and records archived_at', function () {
    $material = Material::factory()->draft()->create();

    $response = $this->actingAs(User::factory()->staff()->create())
        ->patchJson("/materials/{$material->id}/archive")
        ->assertOk()
        ->assertJsonPath('status', MaterialStatus::ARCHIVED->value);

    expect($response->json('archived_at'))->not->toBeNull();
    expect($material->fresh()->archived_at)->not->toBeNull();
    $this->assertDatabaseHas('materials', [
        'id' => $material->id,
        'status' => MaterialStatus::ARCHIVED->value,
    ]);
});

it('returns 422 when category_id does not identify an active category', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->postJson('/materials', [
            'title' => 'Apostila',
            'category_id' => 999999,
            'type' => 'pdf',
            'author' => 'Equipe Pedagógica',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');
});

it('returns 422 when category_id references an inactive category', function () {
    $category = Category::factory()->inactive()->create();

    $this->actingAs(User::factory()->staff()->create())
        ->postJson('/materials', [
            'title' => 'Apostila',
            'category_id' => $category->id,
            'type' => 'pdf',
            'author' => 'Equipe Pedagógica',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');
});

it('resolves materials through a category relationship', function () {
    $category = Category::factory()->create();
    $material = Material::factory()->for($category)->create();

    expect($category->materials()->whereKey($material->getKey())->exists())->toBeTrue();
});

test('teachers can view and download only published materials', function (MaterialStatus $status, bool $canAccess) {
    $teacher = User::factory()->teacher()->create();
    $material = Material::make(['status' => $status]);

    expect(Gate::forUser($teacher)->allows('view', $material))->toBe($canAccess)
        ->and(Gate::forUser($teacher)->allows('download', $material))->toBe($canAccess);
})->with([
    'draft' => [MaterialStatus::DRAFT, false],
    'published' => [MaterialStatus::PUBLISHED, true],
    'archived' => [MaterialStatus::ARCHIVED, false],
]);

test('only admins and staff can manage materials', function (string $role, bool $canManage) {
    $user = match ($role) {
        'admin' => User::factory()->admin()->create(),
        'staff' => User::factory()->staff()->create(),
        default => User::factory()->teacher()->create(),
    };
    $material = Material::factory()->make();

    expect(Gate::forUser($user)->allows('create', Material::class))->toBe($canManage)
        ->and(Gate::forUser($user)->allows('update', $material))->toBe($canManage)
        ->and(Gate::forUser($user)->allows('archive', $material))->toBe($canManage)
        ->and(Gate::forUser($user)->allows('publish', $material))->toBe($canManage);
})->with([
    'admin' => ['admin', true],
    'staff' => ['staff', true],
    'teacher' => ['teacher', false],
]);

it('returns 403 when a teacher tries to publish a material', function () {
    $material = Material::factory()->draft()->create();

    $this->actingAs(User::factory()->teacher()->create())
        ->patchJson("/materials/{$material->id}/publish")
        ->assertForbidden();
});
