<?php

use App\Models\Download;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;

it('shows the complete download ranking for admins and staff in the selected period', function (string $role) {
    $actor = $role === 'admin'
        ? User::factory()->admin()->create()
        : User::factory()->staff()->create();

    $expectedTitles = [];

    foreach ([
        'Mais baixado' => 4,
        'Segundo mais baixado' => 3,
        'Terceiro mais baixado' => 2,
        'Quarto mais baixado' => 1,
    ] as $title => $downloadCount) {
        $material = Material::factory()->published()->create(['title' => $title]);
        $version = MaterialVersion::factory()->for($material)->create();
        $expectedTitles[] = $title;

        Download::factory()->count($downloadCount)->create([
            'material_id' => $material->getKey(),
            'material_version_id' => $version->getKey(),
            'downloaded_at' => now()->subDays(10),
        ]);
    }

    $outsidePeriodMaterial = Material::factory()->published()->create([
        'title' => 'Fora do período',
    ]);
    $outsidePeriodVersion = MaterialVersion::factory()->for($outsidePeriodMaterial)->create();
    Download::factory()->create([
        'material_id' => $outsidePeriodMaterial->getKey(),
        'material_version_id' => $outsidePeriodVersion->getKey(),
        'downloaded_at' => now()->subDays(40),
    ]);

    $this->actingAs($actor)
        ->get(route('painel.materials.most-downloaded', ['downloadPeriod' => '30']))
        ->assertOk()
        ->assertSee('Materiais mais baixados')
        ->assertSee('Quarto mais baixado')
        ->assertDontSee('Fora do período')
        ->assertViewHas('topDownloadedMaterials', function ($materials) use ($expectedTitles): bool {
            return $materials->total() === 4
                && $materials->getCollection()->pluck('title')->all() === $expectedTitles;
        });
})->with([
    'admin' => 'admin',
    'staff' => 'staff',
]);

it('forbids teachers from viewing the download ranking', function () {
    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('painel.materials.most-downloaded'))
        ->assertForbidden();
});

it('redirects guests to login from the download ranking', function () {
    $this->get(route('painel.materials.most-downloaded'))
        ->assertRedirect(route('login'));
});
