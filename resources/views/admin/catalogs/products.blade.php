@extends('layouts.admin')

@section('title', __('Catalog products'))

@section('content')
    <x-admin.page-header
        :title="__('Assign products')"
        :subtitle="$catalog->name"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Catalogs'), 'url' => route('admin.catalogs.index')],
            ['label' => __('Products')],
        ]" />

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.catalogs.products.update', $catalog) }}">
                @csrf
                @method('PUT')

                @if ($products->isEmpty())
                    <x-empty-state :message="__('There are no products to assign yet.')" />
                @else
                    <div class="row g-2">
                        @foreach ($products as $product)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="form-check border rounded p-2 h-100">
                                    <input type="checkbox" id="product-{{ $product->id }}" name="products[]"
                                           value="{{ $product->id }}" class="form-check-input"
                                           @checked(in_array($product->id, old('products', $assigned)))>
                                    <label for="product-{{ $product->id }}" class="form-check-label d-block">
                                        <span class="fw-semibold">{{ $product->name }}</span>
                                        <span class="d-block small text-muted">{{ $product->sku }}</span>
                                        <span class="d-block mt-1">
                                            <x-boolean-badge :value="$product->is_active" />
                                            <x-stock-badge :product="$product" />
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Save assignments') }}</button>
                    <a href="{{ route('admin.catalogs.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

