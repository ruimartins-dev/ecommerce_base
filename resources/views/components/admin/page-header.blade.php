@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => [],
])

{{-- Shared backoffice page header: breadcrumbs, title, optional subtitle and an
     actions slot for create/back buttons. Keeps every index/form view consistent. --}}
<x-breadcrumbs :items="$breadcrumbs" />

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="d-flex flex-wrap gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>

