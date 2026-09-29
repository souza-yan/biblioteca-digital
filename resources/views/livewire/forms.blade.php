<div>
    <x-ts-button x-on:click="$tsui.open.modal('modal-id')" color="blue">
    Open
</x-ts-button>

<x-ts-modal id="modal-id" x-on:open="$tsui.focus('email')"> {{-- [tl! highlight] --}}
    <form>
        <x-ts-input label="Email"
                 id="email" {{-- [tl! highlight] --}}
                 hint="Insert your best email address" />
    </form>
</x-ts-modal>

</div>
