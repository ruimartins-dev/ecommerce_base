@props([
    'message' => null,
])

<div class="text-center text-muted py-5">
    <p class="mb-0">{{ $message ?? __('Nothing to show yet.') }}</p>
    @isset($slot)
        <div class="mt-3">{{ $slot }}</div>
    @endisset
</div>

