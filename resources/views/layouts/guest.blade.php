<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="bg-body-tertiary">
    <main class="min-vh-100 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-sm-10 col-md-8 col-lg-5 col-xl-4">
                    <div class="text-center mb-4">
                        <a href="{{ route('home') }}"
                           class="d-inline-flex align-items-center gap-2 text-decoration-none text-body">
                            <span class="brand-mark" style="width:40px;height:40px;">
                                {{ strtoupper(substr(config('app.name', 'B'), 0, 1)) }}
                            </span>
                            <span class="h5 mb-0 fw-bold">{{ config('app.name', 'B2B Ministore') }}</span>
                        </a>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body p-4 p-sm-5">
                            <x-flash-messages />

                            @yield('content')
                        </div>
                    </div>

                    <p class="text-center text-muted small mt-4 mb-0">
                        &copy; {{ now()->year }} {{ config('app.name', 'B2B Ministore') }}
                    </p>
                </div>
            </div>
        </div>
    </main>

    @stack('scripts')
</body>
</html>

