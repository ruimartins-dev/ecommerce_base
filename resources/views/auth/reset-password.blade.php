@extends('layouts.guest')

@section('title', __('Reset password'))

@section('content')
    <h1 class="h4 mb-4 text-center">{{ __('Reset password') }}</h1>

    <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email', $request->email) }}"
                   required autofocus autocomplete="username"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('New password') }}</label>
            <input type="password" id="password" name="password"
                   required autocomplete="new-password"
                   class="form-control @error('password') is-invalid @enderror">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('Confirm password') }}</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   required autocomplete="new-password" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary w-100">{{ __('Reset password') }}</button>
    </form>
@endsection

