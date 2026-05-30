@extends('layouts.app')

{{-- Backoffice chrome: admin navigation on top of the shared master layout. --}}
@section('navbar')
    <x-navbar :brand="config('app.name').' · Admin'" :home="route('admin.dashboard')">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
               href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.catalogs.*') ? 'active' : '' }}"
               href="{{ route('admin.catalogs.index') }}">{{ __('Catalogs') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
               href="{{ route('admin.categories.index') }}">{{ __('Categories') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
               href="{{ route('admin.products.index') }}">{{ __('Products') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
               href="{{ route('admin.customers.index') }}">{{ __('Customers') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.addresses.*') ? 'active' : '' }}"
               href="{{ route('admin.addresses.index') }}">{{ __('Addresses') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
               href="{{ route('admin.orders.index') }}">{{ __('Orders') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}"
               href="{{ route('admin.audit-logs.index') }}">{{ __('Audit logs') }}</a>
        </li>
    </x-navbar>
@endsection

