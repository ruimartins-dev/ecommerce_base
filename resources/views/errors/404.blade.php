@extends('layouts.app')

@section('title', __('Not found'))

@section('content')
    <div class="text-center py-5">
        <p class="display-4 fw-bold text-secondary mb-2">404</p>
        <h1 class="h4 mb-3">{{ __('Page not found') }}</h1>
        <p class="text-muted mb-4">
            {{ __('The page you are looking for could not be found.') }}
        </p>

        @auth
            <a href="{{ route(auth()->user()->homeRoute()) }}" class="btn btn-primary">
                {{ __('Back to dashboard') }}
            </a>
        @else
            <a href="{{ route('home') }}" class="btn btn-primary">{{ __('Go home') }}</a>
        @endauth
    </div>
@endsection

