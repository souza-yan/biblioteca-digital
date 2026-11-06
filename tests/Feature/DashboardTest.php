<?php

use App\Actions\Dashboard\BuildDashboard;
use App\Enums\ActivityAction;
use App\Livewire\Materials\FavoriteButton;
use App\Livewire\Materials\HomeMaterialSearch;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;

it('shows teachers recent published materials in the educational library home', function () {
    $teacher = User::factory()->teacher()->create();
    $recentMaterial = Material::factory()->published()->create([
        'title' => 'Publicado recentemente',
        'published_at' => now(),
    ]);
    $olderMaterial = Material::factory()->published()->create([
        'title' => 'Publicado anteriormente',
        'published_at' => now()->subDays(2),
    ]);
    $draft = Material::factory()->draft()->create(['title' => 'Rascunho interno']);

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Encontre recursos para suas aulas')
        ->assertSee('Explore por categoria')
        ->assertSee('Materiais publicados recentemente')
        ->assertSee('Publicado recentemente')
        ->assertSee('Publicado anteriormente')
        ->assertDontSee('Rascunho interno')
        ->assertViewHas(
            'publishedMaterials',
            fn ($materials): bool => $materials->every->relationLoaded('currentVersion'),
        )
        ->assertSeeLivewire(HomeMaterialSearch::class)
        ->assertSeeLivewire(FavoriteButton::class);
});

it('shows active categories with their real published material counts to teachers', function () {
    $teacher = User::factory()->teacher()->create();
    $category = Category::factory()->create(['name' => 'Arduino', 'is_active' => true]);
    $inactiveCategory = Category::factory()->create(['name' => 'Categoria inativa', 'is_active' => false]);
    Material::factory()->count(2)->published()->for($category)->create();
    Material::factory()->draft()->for($category)->create();
    Material::factory()->published()->for($inactiveCategory)->create();
    $additionalCategories = Category::factory()->count(4)->create(['is_active' => true]);
    $additionalCategories->each(
        fn (Category $additionalCategory) => Material::factory()->published()->for($additionalCategory)->create(),
    );

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Arduino')
        ->assertSee('2 materiais publicados')
        ->assertSee(route('painel.library', ['categoryFilter' => $category->getKey()]), false)
        ->assertViewHas('categories', function ($categories) use ($category): bool {
            $arduino = $categories->firstWhere('id', $category->getKey());

            return $categories->count() === 5
                && $arduino?->published_materials_count === 2;
        });
});

it('shows teachers only their four latest unique published downloads and previews', function () {
    $teacher = User::factory()->teacher()->create();
    $otherTeacher = User::factory()->teacher()->create();
    $downloaded = Material::factory()->published()->create(['title' => 'Baixado por mim']);
    $previewed = Material::factory()->published()->create(['title' => 'Prévia por mim']);
    $thirdAccess = Material::factory()->published()->create(['title' => 'Terceiro acesso por mim']);
    $olderAccess = Material::factory()->published()->create(['title' => 'Acesso mais antigo por mim']);
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
        [$thirdAccess, $teacher, now()->subMinutes(4)],
        [$olderAccess, $teacher, now()->subMinutes(5)],
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
    expect($recentlyAccessed->pluck('id')->all())->toBe([
        $downloaded->getKey(),
        $previewed->getKey(),
        $thirdAccess->getKey(),
        $olderAccess->getKey(),
    ]);

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Continue de onde parou')
        ->assertSee('Baixado por mim')
        ->assertSee('Prévia por mim')
        ->assertSee('Terceiro acesso por mim')
        ->assertSee('Acesso mais antigo por mim')
        ->assertDontSee('Rascunho acessado')
        ->assertDontSee('Arquivado acessado')
        ->assertViewHas('recentlyAccessedMaterials', function ($materials) use ($downloaded, $previewed, $thirdAccess, $olderAccess): bool {
            return $materials->count() === 4
                && $materials->pluck('id')->all() === [
                    $downloaded->getKey(),
                    $previewed->getKey(),
                    $thirdAccess->getKey(),
                    $olderAccess->getKey(),
                ];
        })
        ->assertSee(route('painel.library.show', $downloaded), false)
        ->assertSee(route('painel.library.show', $previewed), false);
});

it('shows admins and staff totals and the latest ten activities', function (string $role) {
    $actor = $role === 'admin'
        ? User::factory()->admin()->create()
        : User::factory()->staffInSchool()->create();
    $teacher = User::factory()->teacher()->create();
    if ($role === 'staff') {
        $teacher->schools()->attach($actor->school()->getKey(), ['is_active' => true]);
    }
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

    $response = $this->actingAs($actor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('downloadPeriod', '30');

    if ($role === 'staff') {
        $response
            ->assertViewHas('activeUsersCount', 1)
            ->assertSee('Professores vinculados')
            ->assertSee('Materiais publicados')
            ->assertSee('Downloads da escola')
            ->assertDontSee('Atividades recentes')
            ->assertDontSee('Atividade 11');
    } else {
        $response
            ->assertViewHas('activeUsersCount', 2)
            ->assertSee('Usuários ativos')
            ->assertSee('Materiais por status')
            ->assertSee('Rascunho')
            ->assertSee('Publicado')
            ->assertSee('Arquivado')
            ->assertSee('Atividade 11')
            ->assertDontSee('Atividade antiga');
    }
})->with(['admin', 'staff']);

it('shows category counts and top downloaded materials to admins and staff', function (string $role) {
    $actor = $role === 'admin'
        ? User::factory()->admin()->create()
        : User::factory()->staffInSchool()->create();
    $activeCategory = Category::factory()->create(['name' => 'Categoria ativa', 'is_active' => true]);
    Category::factory()->create(['name' => 'Categoria inativa', 'is_active' => false]);
    $topMaterial = Material::factory()->published()->for($activeCategory)->create(['title' => 'Mais baixado']);
    $secondMaterial = Material::factory()->published()->for($activeCategory)->create(['title' => 'Segundo mais baixado']);
    $topVersion = MaterialVersion::factory()->for($topMaterial)->create();
    $secondVersion = MaterialVersion::factory()->for($secondMaterial)->create();
    $downloadUser = User::factory()->teacher()->create();
    if ($role === 'staff') {
        $downloadUser->schools()->attach($actor->school()->getKey(), ['is_active' => true]);
    }

    Download::factory()->count(2)->create([
        'user_id' => $downloadUser->getKey(),
        'material_id' => $topMaterial->getKey(),
        'material_version_id' => $topVersion->getKey(),
        'downloaded_at' => now()->subDays(10),
    ]);
    Download::factory()->create([
        'user_id' => $downloadUser->getKey(),
        'material_id' => $topMaterial->getKey(),
        'material_version_id' => $topVersion->getKey(),
        'downloaded_at' => now()->subDays(40),
    ]);
    Download::factory()->create([
        'user_id' => $downloadUser->getKey(),
        'material_id' => $secondMaterial->getKey(),
        'material_version_id' => $secondVersion->getKey(),
        'downloaded_at' => now()->subDays(10),
    ]);

    $response = $this->actingAs($actor)
        ->get(route('dashboard'))
        ->assertOk()
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

    if ($role === 'staff') {
        $response
            ->assertSee('Materiais mais acessados')
            ->assertDontSee('Categorias')
            ->assertDontSee(route('painel.materials.most-downloaded', ['downloadPeriod' => '30']), false);
    } else {
        $response
            ->assertSee('Categorias')
            ->assertSee('Ativas')
            ->assertSee('Inativas')
            ->assertSee('Materiais mais baixados')
            ->assertSee(route('painel.materials.most-downloaded', ['downloadPeriod' => '30']), false);
    }
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
        ->assertSee('Explore por categoria')
        ->assertSee(route('painel.library.categories'), false)
        ->assertSee('Favoritos')
        ->assertSee('Continue de onde parou')
        ->assertDontSee('Usuários ativos')
        ->assertDontSee('Atividades recentes')
        ->assertDontSee('Gerenciar materiais');
});
