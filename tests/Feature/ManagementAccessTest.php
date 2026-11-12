<?php

use App\Enums\MaterialStatus;
use App\Enums\Role;
use App\Livewire\Activity\ActivityLogIndex;
use App\Livewire\Categories\CategoryManager;
use App\Livewire\Materials\MaterialDetail;
use App\Livewire\Materials\MaterialManager;
use App\Livewire\Materials\OwnContentManager;
use App\Livewire\Profile\UpdateProfileInformationForm;
use App\Livewire\Schools\SchoolManager;
use App\Livewire\Users\UserManager;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

it('blocks teachers from all administrative and management pages', function (string $route) {
    $this->actingAs(User::factory()->teacher()->create())
        ->get(route($route))->assertForbidden();
})->with(['painel.manage-content', 'painel.users', 'painel.categories', 'painel.materials', 'painel.activities', 'painel.schools', 'painel.materials.most-downloaded']);

it('blocks teachers from administrative components even when mounted directly', function (string $component) {
    Livewire::actingAs(User::factory()->teacher()->create())->test($component)->assertForbidden();
})->with([ActivityLogIndex::class, SchoolManager::class, UserManager::class]);

it('blocks teachers from embedding management lists to bypass the page middleware', function (string $component) {
    Livewire::actingAs(User::factory()->teacher()->create())->test($component, ['embedded' => true])->assertForbidden();
})->with([CategoryManager::class, MaterialManager::class]);

it('blocks staff from administrator only pages and components', function (string $route, string $component) {
    $staff = User::factory()->staffInSchool()->create();

    $this->actingAs($staff)->get(route($route))->assertForbidden();
    Livewire::actingAs($staff)->test($component)->assertForbidden();
})->with([
    ['painel.activities', ActivityLogIndex::class],
    ['painel.schools', SchoolManager::class],
]);

it('rechecks permissions after a manager loses their role with a component already open', function (string $componentClass) {
    $staff = User::factory()->staffInSchool()->create();
    $component = Livewire::actingAs($staff)->test($componentClass);

    $staff->forceFill(['role' => Role::TEACHER])->save();

    $component->call('$refresh')->assertForbidden();
})->with([OwnContentManager::class, CategoryManager::class, MaterialManager::class]);

it('does not reveal other creators unpublished material details to staff', function (MaterialStatus $status) {
    $staff = User::factory()->staffInSchool()->create();
    $otherMaterial = Material::factory()->create(['status' => $status]);
    $ownMaterial = Material::factory()->create(['status' => $status, 'created_by' => $staff->getKey()]);

    expect(Gate::forUser($staff)->allows('view', $otherMaterial))->toBeFalse();
    expect(Gate::forUser($staff)->allows('view', $ownMaterial))->toBeTrue();

    $this->actingAs($staff)->get(route('painel.materials.show', $otherMaterial))->assertForbidden();
    $this->get(route('painel.library.show', $otherMaterial))->assertForbidden();
    Livewire::actingAs($staff)->test(MaterialDetail::class, ['material' => $otherMaterial])->assertForbidden();
    $this->get(route('painel.materials.show', $ownMaterial))->assertOk();
})->with([MaterialStatus::DRAFT, MaterialStatus::ARCHIVED]);

it('keeps shared published material details available without allowing changes by other staff', function () {
    $staff = User::factory()->staffInSchool()->create();
    $material = Material::factory()->published()->create();

    $this->actingAs($staff)->get(route('painel.library.show', $material))->assertOk();
    expect(Gate::forUser($staff)->allows('update', $material))->toBeFalse()
        ->and(Gate::forUser($staff)->allows('publish', $material))->toBeFalse()
        ->and(Gate::forUser($staff)->allows('archive', $material))->toBeFalse();
});

it('ignores attempts to promote an account through profile state', function (string $role) {
    $user = $role === 'staff' ? User::factory()->staffInSchool()->create() : User::factory()->teacher()->create();

    Livewire::actingAs($user)->test(UpdateProfileInformationForm::class)
        ->set('state.role', 'admin')->set('state.is_active', false)
        ->call('updateProfileInformation')->assertHasNoErrors();

    $this->assertDatabaseHas('users', ['id' => $user->getKey(), 'role' => $role, 'is_active' => true]);
})->with(['staff', 'teacher']);
