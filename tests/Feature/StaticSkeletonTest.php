<?php

use App\Livewire\Activity\ActivityLogIndex;
use App\Livewire\Categories\CategoryLibrary;
use App\Livewire\Materials\FavoriteLibrary;
use App\Livewire\Materials\MaterialLibrary;
use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialVersion;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Livewire;
use Symfony\Component\Process\Process;

it('renders an accessible static gate while keeping the real content server rendered', function () {
    $html = Blade::render('<x-skeleton.gate name="sample" label="Carregando exemplo"><x-slot:skeleton><x-skeleton.line class="w-40" /></x-slot:skeleton><button>Conteúdo real</button></x-skeleton.gate>');

    expect($html)->toContain('data-skeleton-gate="sample"', 'role="status"', 'aria-busy="true"', 'aria-label="Carregando exemplo"', 'aria-hidden="true"', 'Carregando…', 'x-cloak', '<button>Conteúdo real</button>', 'wire:ignore.self')
        ->not->toContain('animate-', 'x-transition', 'wire:loading', 'wire:lazy', 'app-request-start', 'app-request-end');
});

it('shows skeletons for 650ms on page entry without restarting after interactions', function () {
    $html = view('components.skeleton.gate', [
        'name' => 'sample',
        'label' => 'Carregando',
        'attributes' => new ComponentAttributeBag,
        'skeleton' => 'Carregando',
        'slot' => 'Pronto',
    ])->render();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
    $state = (new DOMXPath($document))->query('//*[@data-skeleton-gate]')->item(0)->getAttribute('x-data');
    $process = new Process(['node', '--input-type=module', '-e', <<<'JS'
        import assert from 'node:assert/strict';
        import vm from 'node:vm';
        let now = 0;
        let sequence = 0;
        const timers = new Map();
        const createState = () => vm.runInNewContext('(' + process.argv[1] + ')', {
            setTimeout(callback, delay) { timers.set(++sequence, { callback, deadline: now + delay }); return sequence; },
            clearTimeout(id) { timers.delete(id); },
        });
        function advance(milliseconds) {
            now += milliseconds;
            for (const [id, timer] of timers) {
                if (timer.deadline <= now) { timers.delete(id); timer.callback(); }
            }
        }
        const state = createState();
        state.start();
        state.start();
        assert.equal(timers.size, 1);
        advance(649);
        assert.equal(state.ready, false);
        advance(1);
        assert.equal(state.ready, true);
        for (let interaction = 0; interaction < 5; interaction++) {
            state.start();
            assert.equal(state.ready, true);
            assert.equal(timers.size, 0);
        }
        const nextPage = createState();
        nextPage.start();
        assert.equal(nextPage.ready, false);
        advance(649);
        assert.equal(nextPage.ready, false);
        advance(1);
        assert.equal(nextPage.ready, true);
        const abandonedPage = createState();
        abandonedPage.start();
        abandonedPage.destroy();
        assert.equal(timers.size, 0);
        JS, $state]);
    $process->run();

    expect($process->getExitCode())->toBe(0, $process->getErrorOutput());
});

it('renders the library gate and real authorized content for each library role', function (string $role) {
    $user = $role === 'teacher' ? User::factory()->teacher()->create() : User::factory()->staffInSchool()->create();
    $material = Material::factory()->published()->create(['title' => 'Material visível']);
    $draft = Material::factory()->draft()->create(['title' => 'Rascunho privado']);

    $this->actingAs($user)->get(route('painel.library'))
        ->assertOk()->assertSee('data-skeleton-gate="material-library"', false)
        ->assertSee($material->title)->assertDontSee($draft->title);
})->with(['teacher', 'staff']);

it('keeps the library gate identity across filtering pagination and favorite updates', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create(['title' => 'Robótica']);

    Livewire::actingAs($teacher)->test(MaterialLibrary::class)
        ->set('search', 'Robótica')->assertSee($material->title)
        ->assertSee('wire:key="skeleton-gate-material-library"', false)
        ->call('gotoPage', 1)->assertSee('wire:key="skeleton-gate-material-library"', false)
        ->call('toggleFavorite', $material->id)->assertSee('wire:key="skeleton-gate-material-library"', false);
});

it('renders favorites normally behind the gate and keeps its identity when removing a favorite', function () {
    $teacher = User::factory()->teacher()->create();
    $material = Material::factory()->published()->create();
    $teacher->favorites()->attach($material);

    $this->actingAs($teacher)->get(route('painel.favorites'))->assertOk()
        ->assertSee('data-skeleton-gate="favorite-library"', false)->assertSee($material->title);
    Livewire::test(FavoriteLibrary::class)->call('removeFavorite', $material->id)
        ->assertDontSee($material->title)->assertSee('wire:key="skeleton-gate-favorite-library"', false);
});

it('renders category skeletons for the correct role and keeps the gate on search', function (string $role) {
    $user = $role === 'teacher' ? User::factory()->teacher()->create() : User::factory()->staffInSchool()->create();
    $category = Category::factory()->create(['name' => 'Categoria visível']);
    Material::factory()->published()->for($category)->create();

    $this->actingAs($user)->get(route('painel.library.categories'))->assertOk()
        ->assertSee('data-skeleton-gate="category-library"', false)
        ->assertSee('data-skeleton-variant="'.$role.'"', false)->assertSee($category->name);
    Livewire::test(CategoryLibrary::class)->set('search', 'Categoria visível')
        ->assertSee($category->name)->assertSee('wire:key="skeleton-gate-category-library"', false);
})->with(['teacher', 'staff']);

it('matches material detail skeleton sections to the authorized role', function (string $role, string $routeName) {
    $user = match ($role) {
        'teacher' => User::factory()->teacher()->create(),
        'staff' => User::factory()->staffInSchool()->create(),
        'admin' => User::factory()->admin()->create(),
    };
    $material = Material::factory()->published()->create(['created_by' => $user->id]);
    $version = MaterialVersion::factory()->for($material)->create();
    $material->update(['current_version_id' => $version->id]);

    $response = $this->actingAs($user)->get(route($routeName, $material))->assertOk()
        ->assertSee('data-skeleton-gate="material-detail"', false)->assertSee($material->title);
    if ($role === 'teacher') {
        $response->assertSee('data-skeleton-section="file-preview"', false)
            ->assertDontSee('data-skeleton-section="version-history"', false)
            ->assertDontSee('data-skeleton-section="version-form"', false);
    } else {
        $response->assertSee('data-skeleton-section="version-history"', false)
            ->assertSee('data-skeleton-section="version-form"', false)
            ->assertDontSee('data-skeleton-section="file-preview"', false);
    }
})->with([['teacher', 'painel.library.show'], ['staff', 'painel.materials.show'], ['admin', 'painel.materials.show']]);

it('renders the dashboard skeleton matching each role without deferring real content', function (string $role, string $heading) {
    $user = match ($role) {
        'teacher' => User::factory()->teacher()->create(),
        'staff' => User::factory()->staffInSchool()->create(),
        'admin' => User::factory()->admin()->create(),
    };

    $this->actingAs($user)->get(route('dashboard'))->assertOk()
        ->assertSee('data-skeleton-gate="dashboard"', false)
        ->assertSee('data-skeleton-variant="'.$role.'"', false)->assertSee($heading)
        ->assertDontSee('wire:lazy', false);
})->with([['teacher', 'Encontre recursos para suas aulas'], ['staff', 'Gestão escolar'], ['admin', 'Visão Geral']]);

it('keeps the activity list gate identity when switching tabs', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('painel.activities'))->assertOk()
        ->assertSee('data-skeleton-gate="activity-list"', false)->assertSee('Atividades');
    Livewire::test(ActivityLogIndex::class)->call('selectTab', 'changes')
        ->assertSee('wire:key="activity-panel"', false)
        ->assertSee('wire:key="skeleton-gate-activity-list"', false)
        ->assertSee('wire:key="filters-changes"', false);
});

it('renders the category management list inside a gate for authorized roles', function (string $role) {
    $user = $role === 'admin' ? User::factory()->admin()->create() : User::factory()->staffInSchool()->create();
    $category = Category::factory()->create(['created_by' => $user->getKey()]);

    $this->actingAs($user)->get(route('painel.categories'))->assertOk()
        ->assertSee('data-skeleton-gate="category-list"', false)->assertSee($category->name);
})->with(['admin', 'staff']);

it('renders the material management list inside a gate for authorized roles', function (string $role) {
    $user = $role === 'admin' ? User::factory()->admin()->create() : User::factory()->staffInSchool()->create();
    $material = Material::factory()->published()->create(['created_by' => $user->id]);

    $this->actingAs($user)->get(route('painel.materials'))->assertOk()
        ->assertSee('data-skeleton-gate="material-list"', false)->assertSee($material->title);
})->with(['admin', 'staff']);

it('renders school cards and managers inside the list gate', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->staffInSchool()->create();
    $school = $manager->schools()->firstOrFail();

    $this->actingAs($admin)->get(route('painel.schools'))->assertOk()
        ->assertSee('data-skeleton-gate="school-list"', false)
        ->assertSee($school->name)->assertSee($manager->name)
        ->assertSee('wire:target="saveSchool"', false);
});

it('renders the user list gate for administrators and school staff', function (string $role) {
    $user = $role === 'admin' ? User::factory()->admin()->create() : User::factory()->staffInSchool()->create();
    $teacher = User::factory()->teacher()->create();
    if ($user->isStaff()) {
        $teacher->schools()->attach($user->schools()->firstOrFail(), ['is_active' => true]);
    }

    $this->actingAs($user)->get(route('painel.users'))->assertOk()
        ->assertSee('data-skeleton-gate="user-list"', false)->assertSee($teacher->name)
        ->assertSee('wire:target="save"', false);
})->with(['admin', 'staff']);

it('renders profile gates without replacing the Jetstream forms', function (string $role) {
    $user = match ($role) {
        'teacher' => User::factory()->teacher()->create(),
        'staff' => User::factory()->staffInSchool()->create(),
        'admin' => User::factory()->admin()->create(),
    };

    $this->actingAs($user)->get(route('profile.show'))->assertOk()
        ->assertSee('data-skeleton-gate="profile-information"', false)
        ->assertSee('data-skeleton-gate="profile-password"', false)
        ->assertSee('data-skeleton-gate="profile-sessions"', false)
        ->assertSee('data-skeleton-gate="profile-security"', false)
        ->assertSee($user->name)->assertSee($user->email)
        ->assertSee('wire:submit="updateProfileInformation"', false)
        ->assertSee('wire:submit="updatePassword"', false)
        ->assertSee('wire:click="confirmLogout"', false);
})->with(['teacher', 'staff', 'admin']);

it('keeps profile skeleton state separate from the Livewire root across updates', function (string $component, string $gate) {
    $this->actingAs(User::factory()->create());
    $form = Livewire::test($component);

    foreach ([$form->html(), $form->call('$refresh')->html()] as $html) {
        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $element = (new DOMXPath($document))->query('//*[@data-skeleton-gate="'.$gate.'"]')->item(0);

        expect($element)->not->toBeNull();
        expect($element->hasAttribute('wire:id'))->toBeFalse();
        expect($element->parentNode->hasAttribute('wire:id'))->toBeTrue();
    }
})->with([
    ['profile.update-profile-information-form', 'profile-information'],
    ['profile.update-password-form', 'profile-password'],
    ['profile.logout-other-browser-sessions-form', 'profile-sessions'],
]);
