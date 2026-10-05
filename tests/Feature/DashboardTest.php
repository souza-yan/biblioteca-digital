<?php

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;

it('shows teachers recent published materials and only their published favorites', function () {
    $teacher = User::factory()->teacher()->create();
    $otherTeacher = User::factory()->teacher()->create();
    $recentMaterial = Material::factory()->published()->create([
        'title' => 'Publicado recentemente',
        'published_at' => now(),
    ]);
    $olderMaterial = Material::factory()->published()->create([
        'title' => 'Publicado anteriormente',
        'published_at' => now()->subDays(2),
    ]);
    $draft = Material::factory()->draft()->create(['title' => 'Rascunho interno']);
    $favorite = Material::factory()->published()->create(['title' => 'Meu favorito']);
    $archivedFavorite = Material::factory()->archived()->create(['title' => 'Favorito arquivado']);
    $otherTeacherFavorite = Material::factory()->published()->create(['title' => 'Favorito de outra pessoa']);
    $teacher->favorites()->attach([$favorite->getKey(), $archivedFavorite->getKey()]);
    $otherTeacher->favorites()->attach($otherTeacherFavorite->getKey());

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Publicado recentemente')
        ->assertSee('Publicado anteriormente')
        ->assertSee('Meu favorito')
        ->assertDontSee('Rascunho interno')
        ->assertDontSee('Favorito arquivado')
        ->assertSee('Favorito de outra pessoa')
        ->assertViewHas(
            'favoriteMaterials',
            fn ($materials): bool => ! $materials->contains('id', $otherTeacherFavorite->getKey()),
        );
});

it('shows admins and staff totals and the latest ten activities', function (string $role) {
    $actor = $role === 'admin'
        ? User::factory()->admin()->create()
        : User::factory()->staff()->create();
    User::factory()->teacher()->create();
    User::factory()->inactive()->teacher()->create();

    $draft = Material::factory()->draft()->create(['created_by' => $actor->getKey()]);
    $published = Material::factory()->published()->create(['created_by' => $actor->getKey()]);
    $archived = Material::factory()->archived()->create(['created_by' => $actor->getKey()]);
    $version = MaterialVersion::factory()->for($published)->create([
        'published_by' => $actor->getKey(),
    ]);

    Download::factory()->create([
        'user_id' => $actor->getKey(),
        'material_id' => $published->getKey(),
        'material_version_id' => $version->getKey(),
        'downloaded_at' => now()->subDays(5),
    ]);
    Download::factory()->create([
        'user_id' => $actor->getKey(),
        'material_id' => $published->getKey(),
        'material_version_id' => $version->getKey(),
        'downloaded_at' => now()->subDays(31),
    ]);

    foreach (range(1, 11) as $sequence) {
        ActivityLog::factory()->withoutMaterial()->create([
            'user_id' => $actor->getKey(),
            'action' => ActivityAction::MATERIAL_CREATED,
            'description' => $sequence === 1 ? 'Atividade antiga' : "Atividade {$sequence}",
            'created_at' => now()->addSeconds($sequence),
        ]);
    }

    $this->actingAs($actor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Usuários ativos')
        ->assertSee('2')
        ->assertSee('Materiais: Rascunho')
        ->assertSee('Materiais: Publicado')
        ->assertSee('Materiais: Arquivado')
        ->assertSee('Downloads nos últimos 30 dias')
        ->assertSee('Atividade 11')
        ->assertDontSee('Atividade antiga');
})->with(['admin', 'staff']);
