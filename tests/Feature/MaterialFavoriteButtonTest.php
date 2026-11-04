<?php

use App\Livewire\Materials\FavoriteButton;
use App\Models\Material;
use App\Models\User;
use Livewire\Livewire;

it('allows a teacher to favorite and unfavorite a published material from a dashboard card', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create();
    $this->actingAs($teacher);

    Livewire::test(FavoriteButton::class, ['material' => $material])
        ->assertSee('Adicionar aos favoritos')
        ->call('toggle')
        ->assertSee('Remover dos favoritos')
        ->assertDispatched('ts-ui:toast', function (string $event, array $parameters): bool {
            return $parameters['type'] === 'success'
                && $parameters['title'] === 'Material favoritado!'
                && $parameters['description'] === 'O material foi adicionado aos seus favoritos.';
        });

    $this->assertDatabaseHas('favorites', [
        'user_id' => $teacher->getKey(),
        'material_id' => $material->getKey(),
    ]);

    Livewire::test(FavoriteButton::class, ['material' => $material, 'isFavorited' => true])
        ->call('toggle')
        ->assertSee('Adicionar aos favoritos')
        ->assertDispatched('ts-ui:toast', function (string $event, array $parameters): bool {
            return $parameters['type'] === 'success'
                && $parameters['title'] === 'Material removido dos favoritos!'
                && $parameters['description'] === 'O material foi removido dos seus favoritos.';
        });

    $this->assertDatabaseMissing('favorites', [
        'user_id' => $teacher->getKey(),
        'material_id' => $material->getKey(),
    ]);
});
