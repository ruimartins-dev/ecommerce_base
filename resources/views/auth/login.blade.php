@extends('layouts.guest')

@section('title', __('Log in'))

@section('content')
    <h1 class="h4 mb-4 text-center">{{ __('Sign in') }}</h1>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input type="password" id="password" name="password"
                   required autocomplete="current-password"
                   class="form-control @error('password') is-invalid @enderror">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label class="form-check-label" for="remember">{{ __('Remember me') }}</label>
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small text-decoration-none">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary w-100">{{ __('Sign in') }}</button>
    </form>
@endsection

