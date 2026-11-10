<?php

use App\Livewire\Materials\MaterialLibrary;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('library query count does not grow with each material card', function () {
    $this->actingAs(User::factory()->teacher()->create());
    $addMaterials = function (int $count): void {
        Material::factory()->published()->count($count)->create()->each(function (Material $material): void {
            $version = MaterialVersion::factory()->for($material)->create();
            $material->update(['current_version_id' => $version->getKey()]);
        });
    };
    $addMaterials(2);
    DB::enableQueryLog();

    try {
        DB::flushQueryLog();
        Livewire::test(MaterialLibrary::class)->assertSee('Materiais publicados');
        $smallListQueries = count(DB::getQueryLog());
        $addMaterials(10);
        DB::flushQueryLog();
        Livewire::test(MaterialLibrary::class)->assertSee('Materiais publicados');
        $largeListQueries = count(DB::getQueryLog());

        expect($largeListQueries)->toBeLessThanOrEqual($smallListQueries);
    } finally {
        DB::disableQueryLog();
    }
});
