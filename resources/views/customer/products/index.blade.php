@extends('layouts.customer')

@section('title', __('Products'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('Account'), 'url' => route('customer.dashboard')],
        ['label' => __('Products')],
    ]" />

    <div class="mb-4">
        <h1 class="h3 mb-1">{{ __('Products') }}</h1>
        <p class="text-muted mb-0">{{ __('Browse the catalogue and add items to your cart.') }}</p>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-12 col-md-6">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="{{ __('Search by name or SKU…') }}">
        </div>
        <div class="col-8 col-md-4">
            <select name="category" class="form-select">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit">{{ __('Filter') }}</button>
        </div>

        @if (request()->hasAny(['search', 'category']))
            <div class="col-12">
                <a href="{{ route('customer.products.index') }}" class="small text-decoration-none">
                    {{ __('Clear filters') }}
                </a>
            </div>
        @endif
    </form>

    @if ($products->isEmpty())
        <x-empty-state :message="__('No products match your search.')" />
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3">
            @foreach ($products as $product)
                <div class="col">
                    <x-customer.product-card :product="$product" />
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    @endif
@endsection

