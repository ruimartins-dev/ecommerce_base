@extends('layouts.admin')

@section('title', __('Products'))

@section('content')
    <x-admin.page-header
        :title="__('Products')"
        :subtitle="__('Manage the product catalog, pricing and stock.')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Products')],
        ]">
        <x-slot:actions>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">{{ __('New product') }}</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-3">
            <input type="search" name="sku" value="{{ request('sku') }}" class="form-control"
                   placeholder="{{ __('Search SKU…') }}">
        </div>
        <div class="col-12 col-md-3">
            <input type="search" name="name" value="{{ request('name') }}" class="form-control"
                   placeholder="{{ __('Search name…') }}">
        </div>
        <div class="col-6 col-md-2">
            <select name="status" class="form-select">
                <option value="">{{ __('Any status') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="stock" class="form-select">
                <option value="">{{ __('Any stock') }}</option>
                <option value="in" @selected(request('stock') === 'in')>{{ __('In stock') }}</option>
                <option value="out" @selected(request('stock') === 'out')>{{ __('Out of stock') }}</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit">{{ __('Filter') }}</button>
        </div>
    </form>

    @if ($products->isEmpty())
        <x-empty-state :message="__('No products found.')" />
    @else
        <x-table :headers="[__('Product'), __('SKU'), __('Price'), __('Stock'), __('Status'), __('Actions')]">
            @foreach ($products as $product)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if ($product->image_path)
                                <img src="{{ Storage::url($product->image_path) }}" alt="" width="40" height="40"
                                     class="rounded object-fit-cover">
                            @endif
                            <span class="fw-semibold">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="small text-muted">{{ $product->sku }}</td>
                    <td>{{ number_format((float) $product->price, 2) }}</td>
                    <td><x-stock-badge :product="$product" /></td>
                    <td><x-boolean-badge :value="$product->is_active" /></td>
                    <td class="text-nowrap">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                            {{ __('Edit') }}
                        </a>
                        <form method="POST" action="{{ route('admin.products.toggle', $product) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                {{ $product->is_active ? __('Deactivate') : __('Activate') }}
                            </button>
                        </form>
                        <x-delete-form :action="route('admin.products.destroy', $product)"
                                       :confirm="__('Delete this product?')" />
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-3">
            {{ $products->links() }}
        </div>
    @endif
@endsection

