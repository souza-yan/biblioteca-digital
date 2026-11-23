@props(['paginator'])

<div {{ $attributes }}>
    {{ $paginator->links('components.pagination-links', ['livewirePagination' => isset($__livewire)]) }}
</div>
