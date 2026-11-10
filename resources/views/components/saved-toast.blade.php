@props([
    'on' => 'saved',
    'title' => 'Alterações salvas!',
    'message' => 'Alterações salvas com sucesso!',
])

<div x-data="{ event: @js($on), title: @js($title), message: @js($message) }"
    x-on:{{ $on }}.window="if ($event.target === $wire.$el) $tsui.interaction('toast').success(title, message).send()"
    hidden {{ $attributes }}></div>
