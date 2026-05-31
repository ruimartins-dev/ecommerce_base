@props([
    'message' => null,
    'title' => null,
    'icon' => 'box',
])

{{-- Intentional empty state: icon, message and optional CTA (default slot). --}}
<div class="card">
    <div class="card-body empty-state">
        <span class="empty-icon">
            <x-icon :name="$icon" size="lg" />
        </span>

        @if ($title)
            <h3 class="h5 mb-1">{{ $title }}</h3>
        @endif

        <p class="text-muted mb-0">{{ $message ?? __('Nothing to show yet.') }}</p>

        @if (trim($slot) !== '')
            <div class="mt-3">{{ $slot }}</div>
        @endif
    </div>
</div>

