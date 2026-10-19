@props(['paginator'])

<div {{ $attributes }}>
    {{ $paginator->links('components.pagination-links') }}
</div>
