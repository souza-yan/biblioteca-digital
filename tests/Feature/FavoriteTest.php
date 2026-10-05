<?php

use App\Livewire\Materials\MaterialDetail;
use App\Livewire\Materials\MaterialLibrary;
use App\Models\Material;
use App\Models\User;
use Livewire\Livewire;

it('allows a teacher to favorite and unfavorite a material from the library', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create();
    $this->actingAs($teacher);

    Livewire::test(MaterialLibrary::class)
        ->call('toggleFavorite', $material->getKey());

    $this->assertDatabaseHas('favorites', [
        'user_id' => $teacher->getKey(),
        'material_id' => $material->getKey(),
    ]);

    Livewire::test(MaterialLibrary::class)
        ->call('toggleFavorite', $material->getKey());

    $this->assertDatabaseMissing('favorites', [
        'user_id' => $teacher->getKey(),
        'material_id' => $material->getKey(),
    ]);
});

it('allows a teacher to toggle a favorite from material details', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create();
    $this->actingAs($teacher);

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->assertSee('Favoritar')
        ->call('toggleFavorite');

    $this->assertDatabaseHas('favorites', [
        'user_id' => $teacher->getKey(),
        'material_id' => $material->getKey(),
    ]);
});

it('does not duplicate a favorite when a teacher toggles it repeatedly', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create();
    $this->actingAs($teacher);

    Livewire::test(MaterialLibrary::class)
        ->call('toggleFavorite', $material->getKey());

    expect($teacher->favorites()->whereKey($material->getKey())->count())->toBe(1);

    Livewire::test(MaterialLibrary::class)
        ->call('toggleFavorite', $material->getKey());

    expect($teacher->favorites()->whereKey($material->getKey())->count())->toBe(0);
});

it('forbids a teacher from favoriting a draft material', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->draft()->create();
    $this->actingAs($teacher);

    Livewire::test(MaterialLibrary::class)
        ->call('toggleFavorite', $material->getKey())
        ->assertForbidden();

    $this->assertDatabaseCount('favorites', 0);
});

it('forbids admins and staff from favoriting materials and viewing the favorites page', function (string $role) {
    $user = $role === 'admin'
        ? User::factory()->admin()->create()
        : User::factory()->staff()->create();
    $material = Material::factory()->published()->create();
    $this->actingAs($user);

    Livewire::test(MaterialDetail::class, ['material' => $material])
        ->call('toggleFavorite')
        ->assertForbidden();

    $this->get(route('painel.favorites'))->assertForbidden();

    $this->assertDatabaseCount('favorites', 0);
})->with([
    'admin' => 'admin',
    'staff' => 'staff',
]);

it('shows teachers only their published favorites and retains archived favorite links', function () {
    $teacher = User::factory()->teacher()->create();
    $otherTeacher = User::factory()->teacher()->create();
    $ownFavorite = Material::factory()->published()->create(['title' => 'Meu material favorito']);
    $otherFavorite = Material::factory()->published()->create(['title' => 'Favorito de outra pessoa']);
    $archivedFavorite = Material::factory()->archived()->create(['title' => 'Favorito arquivado']);
    $teacher->favorites()->attach([$ownFavorite->getKey(), $archivedFavorite->getKey()]);
    $otherTeacher->favorites()->attach($otherFavorite->getKey());
    $this->actingAs($teacher);

    $this->get(route('painel.favorites'))
        ->assertOk()
        ->assertSee($ownFavorite->title)
        ->assertDontSee($otherFavorite->title)
        ->assertDontSee($archivedFavorite->title);

    $this->assertDatabaseHas('favorites', [
        'user_id' => $teacher->getKey(),
        'material_id' => $archivedFavorite->getKey(),
    ]);
});

it('shows the favorites navigation link only to teachers', function () {
    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('painel.favorites'))
        ->assertOk()
        ->assertSee('Favoritos');

    $this->actingAs(User::factory()->staff()->create())
        ->get(route('painel.materials'))
        ->assertOk()
        ->assertDontSee('Favoritos');
});
