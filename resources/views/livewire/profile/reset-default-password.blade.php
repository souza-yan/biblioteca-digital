<div>
    <x-ts-button color="red" type="button" wire:click="openConfirmation"
        wire:loading.attr="disabled" wire:target="openConfirmation"
        aria-label="Redefinir senha para o padrão" title="Redefinir senha para o padrão"
        class="!size-11 !p-0 justify-center">
        <x-ts-icon icon="arrow-path" outline class="size-6 text-white" />
    </x-ts-button>

    <x-ts-modal wire="showConfirmation" title="Redefinir senha" size="md" center>
        <p class="text-base font-semibold text-slate-900">Deseja mesmo redefinir sua senha?</p>
        <p class="mt-2 text-sm text-slate-600">
            Ao redefinir sua senha, ela voltará ao padrão <strong>“password”</strong>.
        </p>
        <div class="mt-4 flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
            <x-ts-icon icon="exclamation-triangle" outline class="mt-0.5 size-5 shrink-0" />
            <p>Você será desconectado imediatamente, e todas as outras sessões da sua conta também serão encerradas.</p>
        </div>

        <x-slot:footer>
            <x-ts-button color="slate" type="button" wire:click="closeConfirmation">
                Cancelar
            </x-ts-button>
            <x-ts-button color="red" type="button" wire:click="resetPassword"
                wire:loading.attr="disabled" wire:target="resetPassword">
                <span wire:loading.remove wire:target="resetPassword">Redefinir senha</span>
                <span wire:loading wire:target="resetPassword">Redefinindo...</span>
            </x-ts-button>
        </x-slot:footer>
    </x-ts-modal>
</div>
