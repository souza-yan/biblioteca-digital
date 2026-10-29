<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithCurrentUser;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class NavigationMenu extends Component
{
    use InteractsWithCurrentUser;

    #[On('refresh-navigation-menu')]
    public function refreshNavigation(): void {}

    public function render(): View
    {
        $currentUser = $this->currentUser();
        $staffSchool = $currentUser->isStaff() ? $currentUser->school() : null;

        return view('livewire.navigation-menu', [
            'currentUser' => $currentUser,
            'navigationItems' => $this->navigationItems(),
            'staffSchool' => $staffSchool,
            'roleLabel' => $currentUser->isStaff() ? 'Gestão escolar' : $currentUser->role->label(),
            'isProfileRoute' => request()->routeIs('profile.show'),
        ]);
    }

    /** @return array<int, array{label: string, route: string, active: bool, icon: string}> */
    private function navigationItems(): array
    {
        $currentUser = $this->currentUser();
        $items = [['label' => 'Início', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard'), 'icon' => 'home']];

        if ($currentUser->isAdmin()) {
            return [...$items,
                ['label' => 'Materiais', 'route' => 'painel.materials', 'active' => request()->routeIs('painel.materials*'), 'icon' => 'book'],
                ['label' => 'Categorias', 'route' => 'painel.categories', 'active' => request()->routeIs('painel.categories'), 'icon' => 'folder'],
                ['label' => 'Professores', 'route' => 'painel.users', 'active' => request()->routeIs('painel.users'), 'icon' => 'users'],
                ['label' => 'Escolas', 'route' => 'painel.schools', 'active' => request()->routeIs('painel.schools'), 'icon' => 'school'],
                ['label' => 'Atividades', 'route' => 'painel.activities', 'active' => request()->routeIs('painel.activities'), 'icon' => 'activity'],
            ];
        }

        if ($currentUser->isStaff()) {
            return [...$items,
                ['label' => 'Professores', 'route' => 'painel.users', 'active' => request()->routeIs('painel.users'), 'icon' => 'users'],
                ['label' => 'Materiais', 'route' => 'painel.library.categories', 'active' => request()->routeIs('painel.library*') || request()->routeIs('painel.materials*') || request()->routeIs('painel.categories'), 'icon' => 'book'],
            ];
        }

        return [...$items,
            ['label' => 'Materiais', 'route' => 'painel.library.categories', 'active' => request()->routeIs('painel.library', 'painel.library.*'), 'icon' => 'folder'],
            ['label' => 'Favoritos', 'route' => 'painel.favorites', 'active' => request()->routeIs('painel.favorites'), 'icon' => 'bookmark'],
        ];
    }
}
