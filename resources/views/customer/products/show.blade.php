@extends('layouts.customer')

@section('title', $product->name)

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('Account'), 'url' => route('customer.dashboard')],
        ['label' => __('Products'), 'url' => route('customer.products.index')],
        ['label' => $product->name],
    ]" />

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="ratio ratio-4x3 bg-light rounded border overflow-hidden">
                @if ($product->image_path)
                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                         class="object-fit-cover w-100 h-100">
                @else
                    <span class="d-flex align-items-center justify-content-center text-muted">
                        {{ __('No image') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <h1 class="h3 mb-1">{{ $product->name }}</h1>
            <p class="text-muted mb-3">{{ __('SKU') }}: {{ $product->sku }}</p>

            @if ($product->categories->isNotEmpty())
                <p class="mb-3">
                    @foreach ($product->categories as $category)
                        <a href="{{ route('customer.categories.show', $category) }}"
                           class="badge text-bg-light text-decoration-none border">{{ $category->name }}</a>
                    @endforeach
                </p>
            @endif

            <p class="h3 mb-3">€{{ number_format((float) $product->price, 2) }}</p>

            <p class="mb-3">
                <x-stock-badge :product="$product" />
                @if ($product->is_out_of_stock)
                    <span class="ms-1 text-muted small">{{ __('Currently unavailable.') }}</span>
                @else
                    <span class="ms-1 text-muted small">{{ __('In stock and ready to ship.') }}</span>
                @endif
            </p>

            @if ($product->is_out_of_stock)
                <button type="button" class="btn btn-outline-secondary" disabled>{{ __('Esgotado') }}</button>
            @else
                <form method="POST" action="{{ route('customer.cart.add') }}" class="row g-2 align-items-end">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="col-auto">
                        <label for="quantity" class="form-label small mb-1">{{ __('Quantity') }}</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1"
                               max="{{ $product->stock }}" class="form-control" style="width: 6rem;">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">{{ __('Add to cart') }}</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h2 class="h6 text-muted text-uppercase mb-3">{{ __('Description') }}</h2>
            <p class="mb-0">{{ $product->description }}</p>
        </div>
    </div>
@endsection

