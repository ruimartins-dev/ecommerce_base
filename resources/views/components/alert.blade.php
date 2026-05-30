@props([
    'type' => 'info',
    'dismissible' => true,
])

@php
    // Map semantic flash types to Bootstrap contextual classes.
    $variant = [
        'success' => 'success',
        'error' => 'danger',
        'danger' => 'danger',
        'warning' => 'warning',
        'info' => 'info',
    ][$type] ?? 'info';

    $classes = 'alert alert-'.$variant.($dismissible ? ' alert-dismissible fade show' : '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} role="alert">
    {{ $slot }}

    @if ($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>

