<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'B2B Ministore'))</title>

    {{-- Bootstrap + app assets compiled by Vite --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @stack('head')
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

    <footer class="border-top py-3 mt-auto bg-white">
        <div class="container text-center text-muted small">
            &copy; {{ now()->year }} {{ config('app.name', 'B2B Ministore') }}
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

