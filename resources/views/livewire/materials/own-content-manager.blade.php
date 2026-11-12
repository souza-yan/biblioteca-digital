<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8" x-data="{ section: 'categories' }">
    <x-skeleton.gate name="own-content-controls" label="Carregando controles de gerenciamento">
        <x-slot:skeleton>
            <div class="space-y-6 lg:space-y-8">
                <h1 class="text-3xl font-bold tracking-tight sm:text-4xl"><x-skeleton.text class="w-fit">Gerenciar Materiais</x-skeleton.text></h1>
                <div class="grid grid-cols-2 gap-1 rounded-xl border border-slate-200 bg-slate-100 p-1 sm:max-w-md">
                    <x-skeleton.block class="h-11 rounded-lg" />
                    <x-skeleton.block class="h-11 rounded-lg" />
                </div>
            </div>
        </x-slot:skeleton>
        <div class="space-y-6 lg:space-y-8">
    <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Gerenciar Materiais</h1>

    <div class="grid grid-cols-2 gap-1 rounded-xl border border-slate-200 bg-slate-100 p-1 sm:max-w-md" role="tablist" aria-label="Tipo de conteúdo">
        <button id="own-categories-tab" type="button" role="tab" aria-controls="own-categories-panel"
            x-bind:aria-selected="section === 'categories'" x-on:click="section = 'categories'"
            x-on:keydown.arrow-right.prevent="section = 'materials'; $refs.materialsTab.focus()"
            x-bind:class="section === 'categories' ? 'bg-blue-800 text-white shadow-sm' : 'text-slate-600 hover:bg-white'"
            class="min-h-11 rounded-lg px-3 py-2 text-sm font-semibold transition-colors" x-ref="categoriesTab">Categorias</button>
        <button id="own-materials-tab" type="button" role="tab" aria-controls="own-materials-panel"
            x-bind:aria-selected="section === 'materials'" x-on:click="section = 'materials'"
            x-on:keydown.arrow-left.prevent="section = 'categories'; $refs.categoriesTab.focus()"
            x-bind:class="section === 'materials' ? 'bg-blue-800 text-white shadow-sm' : 'text-slate-600 hover:bg-white'"
            class="min-h-11 rounded-lg px-3 py-2 text-sm font-semibold transition-colors" x-ref="materialsTab">Materiais</button>
    </div>

        </div>
    </x-skeleton.gate>

    <section id="own-categories-panel" role="tabpanel" aria-labelledby="own-categories-tab" x-show="section === 'categories'">
        <livewire:categories.category-manager :embedded="true" :key="'own-categories-manager'" />
    </section>
    <section id="own-materials-panel" role="tabpanel" aria-labelledby="own-materials-tab" x-show="section === 'materials'" x-cloak>
        <livewire:materials.material-manager :embedded="true" :key="'own-materials-manager'" />
    </section>
</div>
