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
<body>
    {{-- Header / navigation slot (no markup yet - foundation only) --}}
    @include('partials.header')

    <main class="py-4">
        @yield('content')
    </main>

    {{-- Footer slot (no markup yet - foundation only) --}}
    @include('partials.footer')

    @stack('scripts')
</body>
</html>

