@extends('layouts.admin')

@section('title', __('Admin Dashboard'))

@section('content')
    @php($user = auth()->user())

    <x-breadcrumbs :items="[
        ['label' => __('Admin'), 'url' => route('admin.dashboard')],
        ['label' => __('Dashboard')],
    ]" />

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('Backoffice') }}</h1>
            <p class="text-muted mb-0">{{ __('Welcome back, :name.', ['name' => $user->name]) }}</p>
        </div>
        <span class="badge text-bg-primary">{{ $user->role?->name }}</span>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h6 text-muted text-uppercase mb-3">{{ __('Your account') }}</h2>
                    <dl class="row mb-0 small">
                        <dt class="col-4">{{ __('Name') }}</dt>
                        <dd class="col-8">{{ $user->name }}</dd>

                        <dt class="col-4">{{ __('Email') }}</dt>
                        <dd class="col-8">{{ $user->email }}</dd>

                        <dt class="col-4">{{ __('Role') }}</dt>
                        <dd class="col-8">{{ $user->role?->name }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column">
                    <h2 class="h6 text-muted text-uppercase mb-3">{{ __('Management modules') }}</h2>
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('admin.catalogs.index') }}" class="btn btn-outline-primary w-100">{{ __('Catalogs') }}</a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-primary w-100">{{ __('Categories') }}</a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary w-100">{{ __('Products') }}</a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-primary w-100">{{ __('Customers') }}</a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.addresses.index') }}" class="btn btn-outline-primary w-100">{{ __('Addresses') }}</a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary w-100">{{ __('Orders') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

