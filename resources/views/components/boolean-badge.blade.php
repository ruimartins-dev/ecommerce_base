@props([
    'value' => false,
    'true' => null,
    'false' => null,
])

{{-- Generic on/off badge for boolean flags (active, default, …). --}}
<span class="badge {{ $value ? 'text-bg-success' : 'text-bg-secondary' }}">
    {{ $value ? ($true ?? __('Active')) : ($false ?? __('Inactive')) }}
</span>

