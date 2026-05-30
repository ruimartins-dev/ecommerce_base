{{--
    Renders the shared validation error bag as a single Bootstrap alert.
    The $errors variable is always available in every view via Laravel's
    ShareErrorsFromSession middleware, so no props are required.
--}}
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


