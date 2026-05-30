@extends('layouts.app')
@section('title', config('app.name'))
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="h3 mb-3">{{ config('app.name') }}</h1>
                <p class="text-muted mb-0">
                    Foundation is up and running on Laravel {{ app()->version() }}.
                </p>
            </div>
        </div>
    </div>
@endsection
