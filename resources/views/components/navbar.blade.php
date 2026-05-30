@props([
    'brand' => null,
    'home' => '/',
])

{{--
    Reusable top navigation shell. The default slot receives the area-specific
    nav links (as <li class="nav-item"> elements). The authenticated user menu
    and logout control are rendered here so every area gets logout access
    without duplicating markup.
--}}
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ $home }}">
            {{ $brand ?? config('app.name', 'B2B Ministore') }}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#primaryNav" aria-controls="primaryNav"
                aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="primaryNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                {{ $slot }}
            </ul>

            @auth
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text small text-muted">
                                    {{ auth()->user()->email }}
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        {{ __('Log out') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            @endauth
        </div>
    </div>
</nav>

