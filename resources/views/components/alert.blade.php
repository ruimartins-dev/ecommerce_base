@props([
    'type' => 'info',
    'dismissible' => true,
])

@php
    // Map semantic flash types to Bootstrap contextual classes + an icon.
    $variant = [
        'success' => 'success',
        'error' => 'danger',
        'danger' => 'danger',
        'warning' => 'warning',
        'info' => 'info',
    ][$type] ?? 'info';

    $icon = [
        'success' => 'check-circle',
        'danger' => 'alert',
        'warning' => 'alert',
        'info' => 'info',
    ][$variant] ?? 'info';

    $classes = 'alert alert-'.$variant.' d-flex align-items-start gap-2'
        .($dismissible ? ' alert-dismissible fade show' : '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} role="alert">
    <x-icon :name="$icon" class="mt-1 flex-shrink-0" />
    <div class="flex-grow-1">{{ $slot }}</div>

    @if ($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
    @endif
</div>

