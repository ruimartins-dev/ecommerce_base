@props([
    'action',
    'label' => null,
    'confirm' => null,
])

{{-- Destructive action wrapped in a POST+DELETE form with a JS confirmation. --}}
<form method="POST" action="{{ $action }}" class="d-inline"
      onsubmit="return confirm('{{ $confirm ?? __('Are you sure? This action cannot be undone.') }}');">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => 'btn btn-sm btn-outline-danger']) }}>
        {{ $label ?? __('Delete') }}
    </button>
</form>

