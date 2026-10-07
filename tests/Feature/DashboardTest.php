<?php

use App\Actions\Dashboard\BuildDashboard;
use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Category;
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

it('shows teachers only their five latest unique published downloads and previews', function () {
    $teacher = User::factory()->teacher()->create();
    $otherTeacher = User::factory()->teacher()->create();
    $downloaded = Material::factory()->published()->create(['title' => 'Baixado por mim']);
    $previewed = Material::factory()->published()->create(['title' => 'Prévia por mim']);
    $otherAccess = Material::factory()->published()->create(['title' => 'Acesso de outro professor']);
    $draft = Material::factory()->draft()->create(['title' => 'Rascunho acessado']);
    $archived = Material::factory()->archived()->create(['title' => 'Arquivado acessado']);
    $versions = collect([$downloaded, $otherAccess, $draft, $archived])
        ->mapWithKeys(fn (Material $material): array => [
            $material->getKey() => MaterialVersion::factory()->for($material)->create(),
        ]);
    $downloaded->update(['current_version_id' => $versions[$downloaded->getKey()]->getKey()]);
    $otherAccess->update(['current_version_id' => $versions[$otherAccess->getKey()]->getKey()]);
    $draft->update(['current_version_id' => $versions[$draft->getKey()]->getKey()]);
    $archived->update(['current_version_id' => $versions[$archived->getKey()]->getKey()]);

    foreach ([
        [$downloaded, $teacher, now()->subMinutes(2)],
        [$downloaded, $teacher, now()->subMinutes(1)],
        [$previewed, $teacher, now()->subMinutes(3)],
        [$otherAccess, $otherTeacher, now()],
        [$draft, $teacher, now()],
        [$archived, $teacher, now()],
    ] as [$material, $actor, $accessedAt]) {
        ActivityLog::factory()->create([
            'user_id' => $actor->getKey(),
            'material_id' => $material->getKey(),
            'action' => ActivityAction::MATERIAL_PREVIEWED,
            'description' => 'Prévia consultada.',
            'created_at' => $accessedAt,
        ]);
    }

    Download::factory()->create([
        'user_id' => $teacher->getKey(),
        'material_id' => $downloaded->getKey(),
        'material_version_id' => $versions[$downloaded->getKey()]->getKey(),
        'downloaded_at' => now(),
    ]);

    $recentlyAccessed = app(BuildDashboard::class)->handle($teacher)['recentlyAccessedMaterials'];
    expect($recentlyAccessed->pluck('id')->all())->toBe([$downloaded->getKey(), $previewed->getKey()]);

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Últimos materiais acessados ou baixados')
        ->assertSee('Baixado por mim')
        ->assertSee('Prévia por mim')
        ->assertDontSee('Rascunho acessado')
        ->assertDontSee('Arquivado acessado')
        ->assertViewHas('recentlyAccessedMaterials', function ($materials) use ($downloaded, $previewed): bool {
            return $materials->count() === 2
                && $materials->pluck('id')->all() === [$downloaded->getKey(), $previewed->getKey()];
        })
        ->assertSee(route('painel.library.show', $downloaded), false)
        ->assertSee(route('painel.library.show', $previewed), false);
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
        ->assertSee('Materiais por status')
        ->assertSee('Rascunho')
        ->assertSee('Publicado')
        ->assertSee('Arquivado')
        ->assertSee('Nos últimos 30 dias')
        ->assertSee('Atividade 11')
        ->assertDontSee('Atividade antiga');
})->with(['admin', 'staff']);

it('shows category counts and top downloaded materials to admins and staff', function (string $role) {
    $actor = $role === 'admin'
        ? User::factory()->admin()->create()
        : User::factory()->staff()->create();
    $activeCategory = Category::factory()->create(['name' => 'Categoria ativa', 'is_active' => true]);
    Category::factory()->create(['name' => 'Categoria inativa', 'is_active' => false]);
    $topMaterial = Material::factory()->published()->for($activeCategory)->create(['title' => 'Mais baixado']);
    $secondMaterial = Material::factory()->published()->for($activeCategory)->create(['title' => 'Segundo mais baixado']);
    $topVersion = MaterialVersion::factory()->for($topMaterial)->create();
    $secondVersion = MaterialVersion::factory()->for($secondMaterial)->create();

    Download::factory()->count(2)->create([
        'material_id' => $topMaterial->getKey(),
        'material_version_id' => $topVersion->getKey(),
        'downloaded_at' => now()->subDays(10),
    ]);
    Download::factory()->create([
        'material_id' => $topMaterial->getKey(),
        'material_version_id' => $topVersion->getKey(),
        'downloaded_at' => now()->subDays(40),
    ]);
    Download::factory()->create([
        'material_id' => $secondMaterial->getKey(),
        'material_version_id' => $secondVersion->getKey(),
        'downloaded_at' => now()->subDays(10),
    ]);

    $this->actingAs($actor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Categorias')
        ->assertSee('Ativas')
        ->assertSee('Inativas')
        ->assertSee('Materiais mais baixados')
        ->assertSee('Mais baixado')
        ->assertSee('Segundo mais baixado')
        ->assertViewHas('categoryCounts', [
            'total' => 2,
            'active' => 1,
            'inactive' => 1,
        ])
        ->assertViewHas('topDownloadedMaterials', function ($materials): bool {
            return $materials->first()?->downloads_count === 2;
        });
})->with(['admin', 'staff']);

it('uses the default download period when the dashboard period is invalid', function () {
    $actor = User::factory()->admin()->create();
    $material = Material::factory()->published()->create(['title' => 'Contagem padrão']);
    $version = MaterialVersion::factory()->for($material)->create();
    Download::factory()->create([
        'material_id' => $material->getKey(),
        'material_version_id' => $version->getKey(),
        'downloaded_at' => now()->subDays(10),
    ]);
    Download::factory()->create([
        'material_id' => $material->getKey(),
        'material_version_id' => $version->getKey(),
        'downloaded_at' => now()->subDays(60),
    ]);

    $this->actingAs($actor)
        ->get(route('dashboard', ['downloadPeriod' => 'all-time-or-sql']))
        ->assertOk()
        ->assertViewHas('downloadPeriod', '30')
        ->assertViewHas('topDownloadedMaterials', fn ($materials): bool => $materials->first()?->downloads_count === 1);

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('dashboard'))
        ->assertDontSee('Inativas')
        ->assertDontSee('Materiais mais baixados');
});

it('applies the selected 30 day, 90 day, and total download periods', function () {
    $actor = User::factory()->admin()->create();
    $material = Material::factory()->published()->create(['title' => 'Downloads por período']);
    $version = MaterialVersion::factory()->for($material)->create();

    foreach ([5, 60, 120] as $daysAgo) {
        Download::factory()->create([
            'material_id' => $material->getKey(),
            'material_version_id' => $version->getKey(),
            'downloaded_at' => now()->subDays($daysAgo),
        ]);
    }

    $this->actingAs($actor)
        ->get(route('dashboard', ['downloadPeriod' => '30']))
        ->assertViewHas('topDownloadedMaterials', fn ($materials): bool => $materials->first()?->downloads_count === 1);

    $this->get(route('dashboard', ['downloadPeriod' => '90']))
        ->assertViewHas('topDownloadedMaterials', fn ($materials): bool => $materials->first()?->downloads_count === 2);

    $this->get(route('dashboard', ['downloadPeriod' => 'total']))
        ->assertViewHas('topDownloadedMaterials', fn ($materials): bool => $materials->first()?->downloads_count === 3);
});

it('shows teacher navigation without management links or administrative metrics', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Explorar categorias')
        ->assertSee(route('painel.library.categories'), false)
        ->assertSee('Meus favoritos')
        ->assertSee('Últimos materiais acessados ou baixados')
        ->assertDontSee('Usuários ativos')
        ->assertDontSee('Atividades recentes')
        ->assertDontSee('Gerenciar materiais');
});
