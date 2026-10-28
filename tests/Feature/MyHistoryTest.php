<?php

use App\Enums\ActivityAction;
use App\Livewire\Activity\MyHistory;
use App\Models\ActivityLog;
use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Livewire\Livewire;

it('requires authentication to view personal history', function () {
    $this->get(route('painel.history'))->assertRedirect(route('login'));
});

it('forbids administrators from viewing personal history', function () {
    $administrator = User::factory()->admin()->create();

    $this->actingAs($administrator)
        ->get(route('painel.history'))
        ->assertForbidden();

    Livewire::actingAs($administrator)
        ->test(MyHistory::class)
        ->assertForbidden();
});

it('forbids staff from viewing personal history', function () {
    $staff = User::factory()->staffInSchool()->create();

    $this->actingAs($staff)
        ->get(route('painel.history'))
        ->assertForbidden();

    Livewire::actingAs($staff)
        ->test(MyHistory::class)
        ->assertForbidden();
});

it('shows only the authenticated users downloads', function () {
    $teacher = User::factory()->teacher()->create();
    $otherTeacher = User::factory()->teacher()->create();
    $ownMaterial = Material::factory()->published()->create(['title' => 'Meu download']);
    $otherMaterial = Material::factory()->published()->create(['title' => 'Download de outra pessoa']);
    $ownVersion = MaterialVersion::factory()->for($ownMaterial)->create();
    $otherVersion = MaterialVersion::factory()->for($otherMaterial)->create();
    Download::factory()->create([
        'user_id' => $teacher->getKey(),
        'material_id' => $ownMaterial->getKey(),
        'material_version_id' => $ownVersion->getKey(),
    ]);
    Download::factory()->create([
        'user_id' => $otherTeacher->getKey(),
        'material_id' => $otherMaterial->getKey(),
        'material_version_id' => $otherVersion->getKey(),
    ]);

    $this->actingAs($teacher)
        ->get(route('painel.history'))
        ->assertOk()
        ->assertSee('Meu histórico')
        ->assertSee($ownMaterial->title)
        ->assertDontSee($otherMaterial->title)
        ->assertSee(route('painel.library.show', $ownMaterial), false);
});

it('shows only the authenticated users material previews', function () {
    $teacher = User::factory()->teacher()->create();
    $otherTeacher = User::factory()->teacher()->create();
    $ownMaterial = Material::factory()->published()->create(['title' => 'Minha prévia']);
    $otherMaterial = Material::factory()->published()->create(['title' => 'Prévia alheia']);
    ActivityLog::factory()->create([
        'user_id' => $teacher->getKey(),
        'material_id' => $ownMaterial->getKey(),
        'action' => ActivityAction::MATERIAL_PREVIEWED,
    ]);
    ActivityLog::factory()->create([
        'user_id' => $otherTeacher->getKey(),
        'material_id' => $otherMaterial->getKey(),
        'action' => ActivityAction::MATERIAL_PREVIEWED,
    ]);

    Livewire::actingAs($teacher)
        ->test(MyHistory::class)
        ->set('activeTab', 'accesses')
        ->assertSee($ownMaterial->title)
        ->assertDontSee($otherMaterial->title)
        ->set('search', 'inexistente')
        ->assertSee('Nenhum registro encontrado');
});

it('normalizes an invalid personal history tab', function () {
    Livewire::withQueryParams(['activeTab' => 'administration'])
        ->actingAs(User::factory()->teacher()->create())
        ->test(MyHistory::class)
        ->assertSet('activeTab', 'downloads');
});
