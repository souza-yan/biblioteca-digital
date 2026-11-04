<?php

use App\Livewire\Materials\HomeMaterialSearch;
use App\Models\Material;
use App\Models\User;
use Livewire\Livewire;

it('updates published material results while a teacher types', function () {
    $teacher = User::factory()->teacher()->create();
    $matchingTitle = Material::factory()->published()->create([
        'title' => 'Introdução ao Arduino',
        'author' => 'Equipe de Tecnologia',
    ]);
    $matchingAuthor = Material::factory()->published()->create([
        'title' => 'Sensores para iniciantes',
        'author' => 'Professora Arduino',
    ]);
    $otherMaterial = Material::factory()->published()->create([
        'title' => 'Lógica de programação',
        'author' => 'Outro autor',
    ]);
    $draft = Material::factory()->draft()->create([
        'title' => 'Arduino ainda não publicado',
    ]);

    Livewire::actingAs($teacher)
        ->test(HomeMaterialSearch::class)
        ->set('search', 'Arduino')
        ->assertSee($matchingTitle->title)
        ->assertSee($matchingAuthor->title)
        ->assertDontSee($otherMaterial->title)
        ->assertDontSee($draft->title);
});

it('shows published materials alphabetically before a search', function () {
    $teacher = User::factory()->teacher()->create();
    $zebra = Material::factory()->published()->create([
        'title' => 'Zebra',
    ]);
    $abacaxi = Material::factory()->published()->create([
        'title' => 'Abacaxi',
    ]);
    Material::factory()->count(5)->published()->create([
        'title' => 'Material adicional',
    ]);

    Livewire::actingAs($teacher)
        ->test(HomeMaterialSearch::class)
        ->assertSee('Materiais em ordem alfabética')
        ->assertViewHas('materials', function ($materials) use ($abacaxi, $zebra): bool {
            return $materials->count() === 6
                && $materials->first()?->is($abacaxi)
                && $materials->doesntContain($zebra);
        });
});

it('forbids administrators from querying the teacher home search', function () {
    Livewire::actingAs(User::factory()->admin()->create())
        ->test(HomeMaterialSearch::class)
        ->assertForbidden();
});
