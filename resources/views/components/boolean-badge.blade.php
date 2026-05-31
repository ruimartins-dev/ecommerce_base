@props([
    'value' => false,
    'true' => null,
    'false' => null,
])

{{-- Generic on/off badge for boolean flags (active, default, …). --}}
<span class="badge {{ $value ? 'badge-soft-success' : 'badge-soft-secondary' }}">
    <span class="status-dot"></span>
    {{ $value ? ($true ?? __('Active')) : ($false ?? __('Inactive')) }}
</span>

