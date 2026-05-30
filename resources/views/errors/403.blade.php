@extends('layouts.app')

@section('title', __('Forbidden'))

@section('content')
    <div class="text-center py-5">
        <p class="display-4 fw-bold text-danger mb-2">403</p>
        <h1 class="h4 mb-3">{{ __('Access denied') }}</h1>
        <p class="text-muted mb-4">
            {{ $exception?->getMessage() ?: __('You do not have permission to access this page.') }}
        </p>

        @auth
            <a href="{{ route(auth()->user()->homeRoute()) }}" class="btn btn-primary">
                {{ __('Back to dashboard') }}
            </a>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary">{{ __('Sign in') }}</a>
        @endauth
    </div>
@endsection

