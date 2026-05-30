@extends('layouts.customer')

@section('title', $category->name)

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('Account'), 'url' => route('customer.dashboard')],
        ['label' => __('Products'), 'url' => route('customer.products.index')],
        ['label' => $category->name],
    ]" />

    <div class="mb-4">
        <h1 class="h3 mb-1">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="text-muted mb-0">{{ $category->description }}</p>
        @endif
    </div>

    @if ($products->isEmpty())
        <x-empty-state :message="__('No products in this category yet.')">
            <a href="{{ route('customer.products.index') }}" class="btn btn-outline-primary">
                {{ __('Back to all products') }}
            </a>
        </x-empty-state>
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

