@props([
    'brand' => null,
    'home' => '/',
])

{{--
    Reusable storefront top navigation. The default slot receives the
    area-specific nav links (as <li class="nav-item"> elements). The
    authenticated user menu is rendered via the shared <x-user-menu> partial.
--}}
<nav class="navbar navbar-expand-lg storefront-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ $home }}">
            <span class="brand-mark">{{ strtoupper(substr($brand ?? config('app.name', 'B'), 0, 1)) }}</span>
            <span>{{ $brand ?? config('app.name', 'B2B Ministore') }}</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#primaryNav" aria-controls="primaryNav"
                aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <x-icon name="menu" size="lg" />
        </button>

        <div class="collapse navbar-collapse" id="primaryNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-1">
                {{ $slot }}
            </ul>

            <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
                <x-user-menu />
            </div>
        </div>
    </div>
</nav>

