@extends('layouts.admin')

@section('title', __('Admin Dashboard'))

@section('content')
    @php
        $user = auth()->user();

        $modules = [
            ['label' => __('Catalogs'), 'icon' => 'catalog', 'tone' => 'primary', 'href' => route('admin.catalogs.index')],
            ['label' => __('Categories'), 'icon' => 'category', 'tone' => 'info', 'href' => route('admin.categories.index')],
            ['label' => __('Products'), 'icon' => 'product', 'tone' => 'success', 'href' => route('admin.products.index')],
            ['label' => __('Orders'), 'icon' => 'order', 'tone' => 'warning', 'href' => route('admin.orders.index')],
            ['label' => __('Customers'), 'icon' => 'customer', 'tone' => 'secondary', 'href' => route('admin.customers.index')],
            ['label' => __('Addresses'), 'icon' => 'address', 'tone' => 'danger', 'href' => route('admin.addresses.index')],
        ];
    @endphp

    <x-admin.page-header
        :title="__('Backoffice')"
        :subtitle="__('Welcome back, :name.', ['name' => $user->name])"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Dashboard')],
        ]">
        <x-slot:actions>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <x-icon name="plus" /> {{ __('New product') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Quick actions: fast navigation into each management module. --}}
    <h2 class="section-label mb-3">{{ __('Management modules') }}</h2>
    <div class="row g-3 mb-4">
        @foreach ($modules as $module)
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ $module['href'] }}" class="card card-hover h-100 text-body text-decoration-none">
                    <div class="card-body text-center py-4">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2 badge-soft-{{ $module['tone'] }}"
                              style="width:48px;height:48px;">
                            <x-icon :name="$module['icon']" size="lg" />
                        </span>
                        <div class="fw-semibold small">{{ $module['label'] }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="section-label mb-3">{{ __('Your account') }}</h2>
                    <dl class="row mb-0 small">
                        <dt class="col-4 text-muted fw-normal">{{ __('Name') }}</dt>
                        <dd class="col-8 fw-medium">{{ $user->name }}</dd>

                        <dt class="col-4 text-muted fw-normal">{{ __('Email') }}</dt>
                        <dd class="col-8">{{ $user->email }}</dd>

                        <dt class="col-4 text-muted fw-normal">{{ __('Role') }}</dt>
                        <dd class="col-8"><span class="badge badge-soft-primary">{{ $user->role?->name }}</span></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h2 class="section-label mb-3">{{ __('Shortcuts') }}</h2>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary justify-content-start">
                            <x-icon name="order" /> {{ __('Review recent orders') }}
                        </a>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-outline-primary justify-content-start">
                            <x-icon name="plus" /> {{ __('Add a new product') }}
                        </a>
                        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-primary justify-content-start">
                            <x-icon name="audit" /> {{ __('Inspect audit logs') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

