<?php

use App\Actions\Activity\LogActivity;
use App\Actions\Category\CreateCategory;
use App\Actions\Category\ToggleCategoryActive;
use App\Actions\Category\UpdateCategory;
use App\Actions\Download\RecordDownload;
use App\Actions\Material\ArchiveMaterial;
use App\Actions\Material\CreateMaterial;
use App\Actions\Material\PublishMaterial;
use App\Actions\Material\UpdateMaterial;
use App\Actions\MaterialVersion\CreateMaterialVersion;
use App\Actions\User\CreateUser;
use App\Actions\User\ToggleUserActive;
use App\Actions\User\UpdateUser;
use App\Enums\ActivityAction;
use App\Enums\Role;
use App\Livewire\Activity\ActivityLogIndex;
use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('records all supported user, category, material and version actions with their actor', function () {
    Storage::fake('local');
    $actor = User::factory()->admin()->create();

    $createdUser = app(CreateUser::class)->handle($actor, [
        'name' => 'Novo Professor',
        'email' => 'novo.professor@example.com',
        'password' => Hash::make('secret-password'),
        'role' => Role::TEACHER,
        'is_active' => true,
    ]);
    app(UpdateUser::class)->handle($actor, $createdUser, ['name' => 'Professor Atualizado']);
    app(ToggleUserActive::class)->handle($actor, $createdUser);

    $category = app(CreateCategory::class)->handle($actor, [
        'name' => 'Ciências',
        'slug' => 'ciencias',
        'is_active' => true,
    ]);
    app(UpdateCategory::class)->handle($actor, $category, ['name' => 'Ciências Naturais']);
    app(ToggleCategoryActive::class)->handle($actor, $category);

    $material = app(CreateMaterial::class)->handle($actor, [
        'title' => 'Material de Ciências',
        'category_id' => $category->getKey(),
        'type' => 'pdf',
        'author' => 'Equipe Escolar',
    ]);
    app(UpdateMaterial::class)->handle($actor, $material, ['title' => 'Material Atualizado']);
    app(CreateMaterialVersion::class)->handle(
        $material,
        $actor,
        UploadedFile::fake()->create('ciencias.pdf', 20, 'application/pdf'),
        null,
    );
    $material->refresh();
    app(PublishMaterial::class)->handle($actor, $material);
    app(ArchiveMaterial::class)->handle($actor, $material);

    expect(ActivityLog::query()->count())->toBe(11);

    foreach ([
        ActivityAction::USER_CREATED,
        ActivityAction::USER_UPDATED,
        ActivityAction::USER_TOGGLED,
        ActivityAction::CATEGORY_CREATED,
        ActivityAction::CATEGORY_UPDATED,
        ActivityAction::CATEGORY_TOGGLED,
    ] as $action) {
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $actor->getKey(),
            'material_id' => null,
            'action' => $action->value,
        ]);
    }

    foreach ([
        ActivityAction::MATERIAL_CREATED,
        ActivityAction::MATERIAL_UPDATED,
        ActivityAction::VERSION_CREATED,
        ActivityAction::MATERIAL_PUBLISHED,
        ActivityAction::MATERIAL_ARCHIVED,
    ] as $action) {
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $actor->getKey(),
            'material_id' => $material->getKey(),
            'action' => $action->value,
        ]);
    }

    expect(
        ActivityLog::query()
            ->whereIn('action', [ActivityAction::USER_CREATED->value, ActivityAction::USER_UPDATED->value])
            ->pluck('description')
            ->implode(' '),
    )->not->toContain('secret-password');
});

it('records a material download as activity for its authenticated actor', function () {
    $actor = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create();
    $version = MaterialVersion::factory()->for($material)->create();

    app(RecordDownload::class)->handle($actor, $material, $version);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $actor->getKey(),
        'material_id' => $material->getKey(),
        'action' => ActivityAction::MATERIAL_DOWNLOADED->value,
    ]);
});

it('rolls back an action and its activity log when logging fails', function () {
    $actor = User::factory()->staff()->create();
    $logActivity = Mockery::mock(LogActivity::class);
    $logActivity->shouldReceive('handle')->once()->andReturnUsing(
        function (User $actor, ActivityAction $action, string $description, ?Material $material = null): ActivityLog {
            app(LogActivity::class)->handle($actor, $action, $description, $material);

            throw new RuntimeException('Activity storage failed.');
        },
    );

    expect(fn () => (new CreateCategory($logActivity))->handle($actor, [
        'name' => 'Não persistir',
        'slug' => 'nao-persistir',
    ]))->toThrow(RuntimeException::class);

    $this->assertDatabaseCount('categories', 0);
    $this->assertDatabaseCount('activity_logs', 0);
});

it('prevents activity logs from being updated or deleted', function () {
    $activityLog = ActivityLog::factory()->withoutMaterial()->create();
    $originalDescription = $activityLog->description;

    expect(fn () => $activityLog->update(['description' => 'Alterada']))
        ->toThrow(LogicException::class);
    expect(fn () => $activityLog->delete())
        ->toThrow(LogicException::class);

    $this->assertDatabaseHas('activity_logs', [
        'id' => $activityLog->getKey(),
        'description' => $originalDescription,
    ]);
});

it('allows admins and staff to view all activity logs and forbids teachers', function (string $role) {
    $user = match ($role) {
        'admin' => User::factory()->admin()->create(),
        'staff' => User::factory()->staff()->create(),
        default => User::factory()->teacher()->create(),
    };
    ActivityLog::factory()->withoutMaterial()->create(['description' => 'Atividade global']);

    $this->actingAs($user);

    if ($role === 'teacher') {
        $this->get(route('painel.activities'))->assertForbidden();
        Livewire::test(ActivityLogIndex::class)->assertForbidden();

        return;
    }

    $this->get(route('painel.activities'))
        ->assertOk()
        ->assertSee('Atividade global')
        ->assertSee('Atividades');
})->with([
    'admin' => 'admin',
    'staff' => 'staff',
    'teacher' => 'teacher',
]);

it('filters activity logs by user, action, material and date period', function () {
    $admin = User::factory()->admin()->create();
    $actor = User::factory()->staff()->create();
    $otherActor = User::factory()->staff()->create();
    $material = Material::factory()->create(['title' => 'Material filtrável']);
    $otherMaterial = Material::factory()->create(['title' => 'Material não correspondente']);

    ActivityLog::factory()->withoutMaterial()->create([
        'user_id' => $actor->getKey(),
        'action' => ActivityAction::USER_CREATED,
        'description' => 'Correspondência filtrada',
        'created_at' => '2026-09-20 10:00:00',
    ]);
    ActivityLog::factory()->create([
        'user_id' => $actor->getKey(),
        'material_id' => $material->getKey(),
        'action' => ActivityAction::MATERIAL_UPDATED,
        'description' => 'Material correspondente',
        'created_at' => '2026-09-20 10:15:00',
    ]);
    ActivityLog::factory()->create([
        'user_id' => $actor->getKey(),
        'material_id' => $material->getKey(),
        'action' => ActivityAction::MATERIAL_UPDATED,
        'description' => 'Ação diferente',
        'created_at' => '2026-09-20 11:00:00',
    ]);
    ActivityLog::factory()->create([
        'user_id' => $otherActor->getKey(),
        'material_id' => $otherMaterial->getKey(),
        'action' => ActivityAction::USER_CREATED,
        'description' => 'Usuário diferente',
        'created_at' => '2026-09-20 10:30:00',
    ]);
    ActivityLog::factory()->create([
        'user_id' => $actor->getKey(),
        'material_id' => $otherMaterial->getKey(),
        'action' => ActivityAction::MATERIAL_UPDATED,
        'description' => 'Outro material filtrável',
        'created_at' => '2026-09-20 10:20:00',
    ]);
    ActivityLog::factory()->withoutMaterial()->create([
        'user_id' => $actor->getKey(),
        'action' => ActivityAction::USER_CREATED,
        'description' => 'Fora do período',
        'created_at' => '2026-09-22 10:00:00',
    ]);

    $this->actingAs($admin);

    Livewire::test(ActivityLogIndex::class)
        ->set('userFilter', (string) $actor->getKey())
        ->set('actionFilter', ActivityAction::USER_CREATED->value)
        ->set('materialFilter', '')
        ->set('fromDate', '2026-09-20')
        ->set('untilDate', '2026-09-20')
        ->assertSee('Correspondência filtrada')
        ->assertDontSee('Ação diferente')
        ->assertDontSee('Usuário diferente')
        ->assertDontSee('Fora do período');

    Livewire::test(ActivityLogIndex::class)
        ->set('materialFilter', (string) $material->getKey())
        ->set('actionFilter', ActivityAction::MATERIAL_UPDATED->value)
        ->set('fromDate', '2026-09-20')
        ->set('untilDate', '2026-09-20')
        ->assertSee('Material correspondente')
        ->assertDontSee('Outro material filtrável');
});

it('shows the activity navigation link only to admins and staff', function () {
    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('painel.library'))
        ->assertOk()
        ->assertDontSee('Atividades');

    $this->actingAs(User::factory()->staff()->create())
        ->get(route('painel.activities'))
        ->assertOk()
        ->assertSee('Atividades');
});
