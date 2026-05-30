<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'B2B Ministore'))</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="bg-body-tertiary">
    <main class="min-vh-100 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-sm-10 col-md-8 col-lg-5">
                    <div class="text-center mb-4">
                        <a href="{{ route('home') }}" class="h4 text-decoration-none text-body">
                            {{ config('app.name', 'B2B Ministore') }}
                        </a>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <x-flash-messages />

                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @stack('scripts')
</body>
</html>

