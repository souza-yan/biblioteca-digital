@php
    $validationMessage = collect($errors->getBags())->flatMap(fn ($bag) => $bag->all())->first();
    $initialMessage = $validationMessage ?? session('status');
@endphp

<div x-data="{ announcement: '', initialMessage: @js($initialMessage), initialType: @js($validationMessage ? 'error' : 'success') }"
    x-init="$nextTick(() => { if (initialMessage) $tsui.interaction('toast')[initialType](initialType === 'error' ? 'Confira os dados' : 'Concluído', initialMessage).send(); })"
    x-on:ts-ui:toast.window="announcement = ''; $nextTick(() => announcement = [$event.detail.title, $event.detail.description].filter(Boolean).join('. '))"
    class="sr-only" role="status" aria-live="polite" aria-atomic="true" x-text="announcement">
</div>
