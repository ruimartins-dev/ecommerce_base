@props([
    'status',
])

@php
    // Map each order status to a Bootstrap contextual colour.
    $variant = [
        'pending' => 'secondary',
        'confirmed' => 'info',
        'processing' => 'primary',
        'shipped' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger',
    ][$status->value] ?? 'secondary';
@endphp

{{-- data-status lets the realtime JS swap the colour/label without a reload. --}}
<span {{ $attributes->merge(['class' => "badge text-bg-{$variant}"]) }}
      data-status="{{ $status->value }}">{{ $status->label() }}</span>

