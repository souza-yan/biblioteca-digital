<?php

use App\Enums\ActivityAction;
use App\Livewire\Activity\ActivityLogIndex;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

it('shows downloads, changes and access events only in their matching tabs', function () {
    $actor = User::factory()->staff()->create();
    $category = Category::factory()->create(['name' => 'Ciências']);
    $material = Material::factory()->for($category)->create(['title' => 'Livro de Ciências']);
    $version = MaterialVersion::factory()->for($material)->create(['version_number' => 3]);
    Download::factory()->create([
        'user_id' => $actor->getKey(),
        'material_id' => $material->getKey(),
        'material_version_id' => $version->getKey(),
        'downloaded_at' => '2026-10-01 10:20:00',
    ]);
    ActivityLog::factory()->withoutMaterial()->create([
        'user_id' => $actor->getKey(),
        'action' => ActivityAction::USER_CREATED,
        'description' => 'Professor cadastrado',
    ]);
    ActivityLog::factory()->withoutMaterial()->create([
        'user_id' => $actor->getKey(),
        'action' => ActivityAction::AUTH_LOGIN,
        'description' => 'Login realizado.',
    ]);
    ActivityLog::factory()->create([
        'user_id' => $actor->getKey(),
        'material_id' => $material->getKey(),
        'action' => ActivityAction::MATERIAL_PREVIEWED,
        'description' => 'Prévia do material consultada.',
    ]);

    Livewire::actingAs($actor)
        ->test(ActivityLogIndex::class)
        ->assertSet('activeTab', 'downloads')
        ->assertSee('Livro de Ciências')
        ->assertSee('Ciências')
        ->assertDontSee('Professor cadastrado')
        ->set('activeTab', 'changes')
        ->assertSee('Professor cadastrado')
        ->assertDontSee('Login realizado.')
        ->assertDontSee('Prévia do material consultada.')
        ->set('activeTab', 'accesses')
        ->assertSee('Login realizado.')
        ->assertSee('Prévia do material consultada.')
        ->assertDontSee('Professor cadastrado');
});

it('filters downloads by user, material and period', function () {
    $admin = User::factory()->admin()->create();
    $selectedUser = User::factory()->teacher()->create(['name' => 'Professor alvo']);
    $otherUser = User::factory()->teacher()->create(['name' => 'Outro professor']);
    $selectedMaterial = Material::factory()->create(['title' => 'Material alvo']);
    $otherMaterial = Material::factory()->create(['title' => 'Outro material']);
    $selectedVersion = MaterialVersion::factory()->for($selectedMaterial)->create();
    $otherVersion = MaterialVersion::factory()->for($otherMaterial)->create();
    Download::factory()->create([
        'user_id' => $selectedUser->getKey(),
        'material_id' => $selectedMaterial->getKey(),
        'material_version_id' => $selectedVersion->getKey(),
        'downloaded_at' => '2026-10-01 11:00:00',
    ]);
    Download::factory()->create([
        'user_id' => $otherUser->getKey(),
        'material_id' => $otherMaterial->getKey(),
        'material_version_id' => $otherVersion->getKey(),
        'downloaded_at' => '2026-10-02 11:00:00',
    ]);

    Livewire::actingAs($admin)
        ->test(ActivityLogIndex::class)
        ->set('downloadsUserFilter', (string) $selectedUser->getKey())
        ->set('downloadsMaterialFilter', (string) $selectedMaterial->getKey())
        ->set('downloadsFromDate', '2026-10-01')
        ->set('downloadsUntilDate', '2026-10-01')
        ->assertViewHas('records', fn ($records): bool => $records->total() === 1)
        ->assertSee('Professor alvo')
        ->assertSee('Material alvo');
});

it('filters access events by user and period', function () {
    $admin = User::factory()->admin()->create();
    $otherUser = User::factory()->staff()->create();
    ActivityLog::factory()->withoutMaterial()->create([
        'user_id' => $admin->getKey(),
        'action' => ActivityAction::AUTH_LOGIN,
        'description' => 'Acesso filtrado',
        'created_at' => '2026-10-01 09:00:00',
    ]);
    ActivityLog::factory()->withoutMaterial()->create([
        'user_id' => $otherUser->getKey(),
        'action' => ActivityAction::AUTH_LOGOUT,
        'description' => 'Outro usuário',
        'created_at' => '2026-10-01 10:00:00',
    ]);
    ActivityLog::factory()->withoutMaterial()->create([
        'user_id' => $admin->getKey(),
        'action' => ActivityAction::AUTH_LOGIN,
        'description' => 'Fora do período',
        'created_at' => '2026-10-02 09:00:00',
    ]);

    Livewire::actingAs($admin)
        ->test(ActivityLogIndex::class)
        ->set('activeTab', 'accesses')
        ->set('accessesUserFilter', (string) $admin->getKey())
        ->set('accessesFromDate', '2026-10-01')
        ->set('accessesUntilDate', '2026-10-01')
        ->assertSee('Acesso filtrado')
        ->assertDontSee('Outro usuário')
        ->assertDontSee('Fora do período');
});

it('preserves filters for each tab while switching between them', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->create();
    $version = MaterialVersion::factory()->for($material)->create();
    Download::factory()->create([
        'user_id' => $teacher->getKey(),
        'material_id' => $material->getKey(),
        'material_version_id' => $version->getKey(),
    ]);

    Livewire::actingAs($admin)
        ->test(ActivityLogIndex::class)
        ->set('downloadsUserFilter', (string) $teacher->getKey())
        ->set('downloadsMaterialFilter', (string) $material->getKey())
        ->set('activeTab', 'changes')
        ->set('changesActionFilter', ActivityAction::USER_CREATED->value)
        ->set('activeTab', 'accesses')
        ->set('accessesUserFilter', (string) $admin->getKey())
        ->set('activeTab', 'downloads')
        ->assertSet('downloadsUserFilter', (string) $teacher->getKey())
        ->assertSet('downloadsMaterialFilter', (string) $material->getKey())
        ->set('activeTab', 'changes')
        ->assertSet('changesActionFilter', ActivityAction::USER_CREATED->value)
        ->set('activeTab', 'accesses')
        ->assertSet('accessesUserFilter', (string) $admin->getKey());
});

it('shows each tab filters and clears only the active tab filters', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->create();

    Livewire::actingAs($admin)
        ->test(ActivityLogIndex::class)
        ->assertSee('wire:model.live="downloadsUserFilter"', false)
        ->assertSee('wire:model.live="downloadsMaterialFilter"', false)
        ->assertSee('wire:click="clearDownloadsFilters"', false)
        ->set('downloadsUserFilter', (string) $teacher->getKey())
        ->set('downloadsMaterialFilter', (string) $material->getKey())
        ->set('downloadsFromDate', '2026-10-01')
        ->set('downloadsUntilDate', '2026-10-02')
        ->set('activeTab', 'changes')
        ->assertSee('wire:model.live="changesActionFilter"', false)
        ->assertSee('wire:click="clearChangesFilters"', false)
        ->set('changesUserFilter', (string) $teacher->getKey())
        ->set('changesActionFilter', ActivityAction::USER_CREATED->value)
        ->set('changesFromDate', '2026-10-03')
        ->set('changesUntilDate', '2026-10-04')
        ->set('activeTab', 'accesses')
        ->assertSee('wire:model.live="accessesUserFilter"', false)
        ->assertSee('wire:click="clearAccessesFilters"', false)
        ->set('accessesUserFilter', (string) $admin->getKey())
        ->set('accessesFromDate', '2026-10-05')
        ->set('accessesUntilDate', '2026-10-06')
        ->set('activeTab', 'downloads')
        ->call('clearDownloadsFilters')
        ->assertSet('downloadsUserFilter', '')
        ->assertSet('downloadsMaterialFilter', '')
        ->assertSet('downloadsFromDate', '')
        ->assertSet('downloadsUntilDate', '')
        ->assertSet('changesUserFilter', (string) $teacher->getKey())
        ->assertSet('changesActionFilter', ActivityAction::USER_CREATED->value)
        ->assertSet('accessesUserFilter', (string) $admin->getKey())
        ->set('activeTab', 'changes')
        ->call('clearChangesFilters')
        ->assertSet('changesUserFilter', '')
        ->assertSet('changesActionFilter', '')
        ->assertSet('changesFromDate', '')
        ->assertSet('changesUntilDate', '')
        ->assertSet('accessesUserFilter', (string) $admin->getKey())
        ->set('activeTab', 'accesses')
        ->call('clearAccessesFilters')
        ->assertSet('accessesUserFilter', '')
        ->assertSet('accessesFromDate', '')
        ->assertSet('accessesUntilDate', '');
});

it('registers login and logout events without logging credentials', function () {
    $user = User::factory()->staff()->create();

    Event::dispatch(new Login('web', $user, false));
    Event::dispatch(new Logout('web', $user));

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user->getKey(),
        'material_id' => null,
        'action' => ActivityAction::AUTH_LOGIN->value,
        'description' => 'Login realizado.',
    ]);
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user->getKey(),
        'material_id' => null,
        'action' => ActivityAction::AUTH_LOGOUT->value,
        'description' => 'Logout realizado.',
    ]);
    expect(
        ActivityLog::query()->whereIn('action', [
            ActivityAction::AUTH_LOGIN,
            ActivityAction::AUTH_LOGOUT,
        ])->pluck('description')->implode(' '),
    )->not->toContain($user->email);
});

it('does not create access logs for failed login attempts', function () {
    $user = User::factory()->staff()->create();

    Event::dispatch(new Failed('web', $user, [
        'email' => $user->email,
        'password' => 'never-store-this',
    ]));

    expect(ActivityLog::query()->count())->toBe(0);
});

it('does not log logout after the user record has already been removed', function () {
    $user = User::factory()->staff()->create();
    $user->delete();

    Event::dispatch(new Logout('web', $user));

    expect(ActivityLog::query()->count())->toBe(0);
});

it('keeps access and changes pagination independent and rejects teacher access', function () {
    $admin = User::factory()->admin()->create();
    ActivityLog::factory()->count(25)->withoutMaterial()->create([
        'user_id' => $admin->getKey(),
        'action' => ActivityAction::AUTH_LOGIN,
    ]);
    ActivityLog::factory()->count(25)->withoutMaterial()->create([
        'user_id' => $admin->getKey(),
        'action' => ActivityAction::USER_CREATED,
    ]);

    Livewire::actingAs($admin)
        ->test(ActivityLogIndex::class)
        ->set('activeTab', 'accesses')
        ->call('setPage', 2, 'accessesPage')
        ->assertSet('paginators.accessesPage', 2)
        ->set('activeTab', 'changes')
        ->call('setPage', 2, 'changesPage')
        ->assertSet('paginators.changesPage', 2)
        ->set('activeTab', 'accesses')
        ->assertSet('paginators.accessesPage', 2);

    Livewire::actingAs(User::factory()->teacher()->create())
        ->test(ActivityLogIndex::class)
        ->assertForbidden();
});
