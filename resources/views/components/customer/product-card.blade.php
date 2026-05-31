@props([
    'product',
])

{{-- Reusable storefront product card shared by the catalogue and category
     pages. Renders image, identity, categories, price, stock state and an
     add-to-cart control that is disabled when the product is out of stock. --}}
<div class="card h-100 card-hover">
    <a href="{{ route('customer.products.show', $product) }}"
       class="ratio ratio-4x3 bg-light d-block text-decoration-none rounded-top overflow-hidden">
        @if ($product->image_path)
            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                 class="object-fit-cover w-100 h-100">
        @else
            <span class="d-flex align-items-center justify-content-center text-muted">
                <x-icon name="product" size="xl" />
            </span>
        @endif
    </a>

    <div class="card-body d-flex flex-column">
        <h2 class="h6 mb-1">
            <a href="{{ route('customer.products.show', $product) }}"
               class="text-decoration-none text-reset">
                {{ $product->name }}
            </a>
        </h2>
        <p class="text-muted small mb-2">{{ __('SKU') }}: {{ $product->sku }}</p>

        @if ($product->categories->isNotEmpty())
            <p class="mb-2 d-flex flex-wrap gap-1">
                @foreach ($product->categories as $category)
                    <a href="{{ route('customer.categories.show', $category) }}"
                       class="badge badge-soft-secondary text-decoration-none">{{ $category->name }}</a>
                @endforeach
            </p>
        @endif

        <div class="mt-auto">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="h5 mb-0">€{{ number_format((float) $product->price, 2) }}</span>
                <x-stock-badge :product="$product" />
            </div>

            @if ($product->is_out_of_stock)
                <button type="button" class="btn btn-outline-secondary w-100" disabled>
                    {{ __('Esgotado') }}
                </button>
            @else
                <form method="POST" action="{{ route('customer.cart.add') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-primary w-100">
                        <x-icon name="cart" /> {{ __('Add to cart') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

