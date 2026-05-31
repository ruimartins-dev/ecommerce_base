<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="d-flex flex-column min-vh-100 bg-body-tertiary">
    {{-- Area-specific navigation is injected by child layouts. --}}
    @yield('navbar')

    <main class="flex-grow-1 py-4">
        <div class="container">
            {{-- Global UX: flash messages + validation errors. --}}
            <x-flash-messages />
            <x-validation-errors />

            @yield('content')
        </div>
    </main>

    <footer class="border-top py-4 mt-auto bg-white">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2 text-muted small">
            <span>&copy; {{ now()->year }} {{ config('app.name', 'B2B Ministore') }}</span>
            <span class="text-muted">{{ __('B2B commerce platform') }}</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

