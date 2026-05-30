@extends('layouts.guest')

@section('title', __('Forgot password'))

@section('content')
    <h1 class="h4 mb-3 text-center">{{ __('Forgot your password?') }}</h1>

    <p class="text-muted small mb-4">
        {{ __('Enter your email address and we will send you a link to reset your password.') }}
    </p>

    <form method="POST" action="{{ route('password.email') }}" novalidate>
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

        <button type="submit" class="btn btn-primary w-100">
            {{ __('Email password reset link') }}
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="small text-decoration-none">{{ __('Back to sign in') }}</a>
    </div>
@endsection

