<?php

use App\Livewire\Dashboard\TeacherDashboardContent;
use App\Livewire\Materials\HomeMaterialSearch;
use App\Livewire\Materials\MaterialDetail;
use App\Livewire\Materials\MaterialLibrary;
use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Livewire\Livewire;

it('serves accessible element placeholders before loading the teacher home content', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Encontre recursos para suas aulas')
        ->assertSee('aria-label="Carregando busca de materiais"', false)
        ->assertSee('aria-label="Carregando categorias"', false)
        ->assertSee('aria-label="Carregando materiais publicados recentemente"', false)
        ->assertSee('aria-label="Carregando materiais acessados recentemente"', false)
        ->assertSee('aria-busy="true"', false)
        ->assertSee('x-bind:aria-busy="(!skeletonFailed).toString()"', false)
        ->assertSee('Não foi possível carregar esta área.')
        ->assertDontSee('data-app-loading-skeleton', false);
});

it('loads the real teacher home content after its placeholder', function () {
    $teacher = User::factory()->teacher()->create();
    $category = Category::factory()->create(['name' => 'Arduino']);
    $material = Material::factory()->published()->for($category)->create([
        'title' => 'Introdução aos sensores',
    ]);

    Livewire::withoutLazyLoading()
        ->actingAs($teacher)
        ->test(TeacherDashboardContent::class)
        ->assertSee('Explore por categoria')
        ->assertSee($category->name)
        ->assertSee('Materiais publicados recentemente')
        ->assertSee($material->title)
        ->assertSee('aria-busy="false"', false);
});

it('targets only home search results while an asynchronous search is loading', function () {
    $teacher = User::factory()->teacher()->create();
    Material::factory()->published()->create(['title' => 'Arduino básico']);

    Livewire::actingAs($teacher)
        ->test(HomeMaterialSearch::class)
        ->assertSee('wire:loading.grid', false)
        ->assertSee('wire:loading.remove', false)
        ->assertSee('wire:target="search"', false)
        ->set('search', 'Arduino')
        ->assertSee('Arduino básico');
});

it('serves the library placeholder and targets only its result grid while filtering', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create(['title' => 'Programação em blocos']);

    $this->actingAs($teacher)
        ->get(route('painel.library'))
        ->assertOk()
        ->assertSee('aria-label="Carregando biblioteca de materiais"', false)
        ->assertSee('aria-busy="true"', false)
        ->assertSee('Não foi possível carregar os materiais.')
        ->assertDontSee('data-app-loading-skeleton', false);

    Livewire::actingAs($teacher)
        ->test(MaterialLibrary::class)
        ->assertSee($material->title)
        ->assertSee('wire:loading.grid', false)
        ->assertSee('wire:loading.remove', false)
        ->assertSee('wire:target="search,authorFilter,categoryFilter,titleSortDirection,toggleTitleSort,applyRecentSearch,setPage,previousPage,nextPage,gotoPage"', false)
        ->assertSee('aria-busy="false"', false);
});

it('serves role-specific material detail twins and targets only version history while saving', function () {
    $teacher = User::factory()->teacher()->create();
    $publishedMaterial = Material::factory()->published()->create();

    $this->actingAs($teacher)
        ->get(route('painel.library.show', $publishedMaterial))
        ->assertSee('aria-label="Carregando detalhes do material"', false)
        ->assertDontSee('Atualizando histórico de versões…')
        ->assertDontSee('data-app-loading-skeleton', false);

    $admin = User::factory()->admin()->create();
    $managedMaterial = Material::factory()->create(['created_by' => $admin->getKey()]);
    MaterialVersion::factory()->for($managedMaterial)->create();

    $this->actingAs($admin)
        ->get(route('painel.materials.show', $managedMaterial))
        ->assertSee('aria-label="Carregando detalhes do material"', false)
        ->assertSee('Não foi possível carregar os detalhes do material.');

    Livewire::actingAs($admin)
        ->test(MaterialDetail::class, ['material' => $managedMaterial])
        ->assertSee('wire:loading.block', false)
        ->assertSee('wire:loading.remove', false)
        ->assertSee('wire:target="saveVersion"', false)
        ->assertSee('aria-busy="true"', false);
});
