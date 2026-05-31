{{-- Shared document head. Keeps meta/asset wiring in one place. --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#4f46e5">

<title>@yield('title', config('app.name', 'B2B Ministore'))</title>

{{-- Bootstrap + app assets compiled by Vite --}}
@vite(['resources/sass/app.scss', 'resources/js/app.js'])

@stack('head')

