{{--
    Renders session flash messages as Bootstrap alerts.
    Supported keys: success, error, warning, info, plus Laravel's "status"
    (used by the password broker) which is shown as an info alert.
--}}
@php
    $messages = [
        'success' => 'success',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info',
        'status' => 'info',
    ];
@endphp

@foreach ($messages as $key => $type)
    @if (session()->has($key))
        <x-alert :type="$type">{{ session($key) }}</x-alert>
    @endif
@endforeach

