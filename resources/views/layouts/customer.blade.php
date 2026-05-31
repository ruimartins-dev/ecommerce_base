@extends('layouts.app')

{{-- Frontoffice chrome: customer navigation on top of the shared master layout. --}}
@section('navbar')
    @php($cartCount = app(\App\Services\CartService::class)->count())

    <x-navbar :brand="config('app.name')" :home="route('customer.dashboard')">
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}"
               href="{{ route('customer.dashboard') }}">
                <x-icon name="dashboard" /> {{ __('Dashboard') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('customer.products.*') || request()->routeIs('customer.categories.*') ? 'active' : '' }}"
               href="{{ route('customer.products.index') }}">
                <x-icon name="product" /> {{ __('Products') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('customer.cart.*') ? 'active' : '' }}"
               href="{{ route('customer.cart.index') }}">
                <x-icon name="cart" /> {{ __('Cart') }}
                @if ($cartCount > 0)
                    <span class="badge text-bg-primary rounded-pill">{{ $cartCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('customer.orders.*') ? 'active' : '' }}"
               href="{{ route('customer.orders.index') }}">
                <x-icon name="order" /> {{ __('My Orders') }}
            </a>
        </li>
    </x-navbar>
@endsection

