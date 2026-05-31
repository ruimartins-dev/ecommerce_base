{{--
    Renders the shared validation error bag as a single Bootstrap alert.
    The $errors variable is normally shared into every view by Laravel's
    ShareErrorsFromSession middleware (part of the "web" group). However,
    error pages such as 404/403 can be rendered for requests that never
    passed through that middleware, in which case $errors is undefined.
    We fall back to an empty bag so the component is always safe to use.
--}}
@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
@endphp

@if ($errors->any())
    <x-alert type="error">
        <p class="mb-2 fw-semibold">{{ __('Please fix the following:') }}</p>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif


