<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="bg-body-tertiary">
    {{-- Backoffice chrome: persistent sidebar + sticky topbar shell. --}}
    <div class="app-shell">
        <x-admin.sidebar />

        <div class="app-main">
            <x-admin.topbar />

            <main class="app-content">
                <div class="container-fluid px-0">
                    {{-- Global UX: flash messages + validation errors. --}}
                    <x-flash-messages />
                    <x-validation-errors />

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

