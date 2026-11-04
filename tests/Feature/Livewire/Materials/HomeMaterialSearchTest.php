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

it('shows the six latest published materials before a search', function () {
    $teacher = User::factory()->teacher()->create();
    $oldestMaterial = Material::factory()->published()->create([
        'title' => 'Material publicado mais antigo',
        'published_at' => now()->subDays(10),
    ]);
    Material::factory()->count(6)->published()->create([
        'published_at' => now(),
    ]);

    Livewire::actingAs($teacher)
        ->test(HomeMaterialSearch::class)
        ->assertViewHas('materials', fn ($materials): bool => $materials->count() === 6)
        ->assertDontSee($oldestMaterial->title);
});

it('forbids administrators from querying the teacher home search', function () {
    Livewire::actingAs(User::factory()->admin()->create())
        ->test(HomeMaterialSearch::class)
        ->assertForbidden();
});
