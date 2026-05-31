@props([
    'label' => null,
])

{{--
    Compact row-actions menu for tables. Keeps action columns tidy instead of a
    wall of buttons. The default slot receives <li> dropdown items (links,
    <button>s, divider <hr class="dropdown-divider">, or inline forms).
--}}
<div class="dropdown">
    <button class="btn btn-sm btn-light border btn-icon" type="button"
            data-bs-toggle="dropdown" aria-expanded="false"
            aria-label="{{ $label ?? __('Actions') }}">
        <x-icon name="dots" />
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow">
        {{ $slot }}
    </ul>
</div>

