@props([
    'label',
    'value',
    'icon' => 'box',
    'tone' => 'primary', // primary | success | info | warning | danger | secondary
    'href' => null,
    'hint' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

{{-- Compact KPI / summary card. Use ONLY with real data — never fabricated. --}}
<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'card h-100 text-body text-decoration-none'.($href ? ' card-hover' : '')]) }}>
    <div class="card-body d-flex align-items-center gap-3">
        <span class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0
                     badge-soft-{{ $tone }}" style="width:44px;height:44px;">
            <x-icon :name="$icon" size="lg" />
        </span>
        <div class="min-w-0">
            <div class="section-label">{{ $label }}</div>
            <div class="h4 mb-0 fw-bold">{{ $value }}</div>
            @if ($hint)
                <div class="small text-muted">{{ $hint }}</div>
            @endif
        </div>
    </div>
</{{ $tag }}>

