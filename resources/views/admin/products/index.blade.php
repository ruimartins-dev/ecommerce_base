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
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <x-icon name="plus" /> {{ __('New product') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-12 col-md-3">
                <input type="search" name="sku" value="{{ request('sku') }}" class="form-control"
                       placeholder="{{ __('Search SKU…') }}" aria-label="{{ __('Search SKU') }}">
            </div>
            <div class="col-12 col-md-3">
                <input type="search" name="name" value="{{ request('name') }}" class="form-control"
                       placeholder="{{ __('Search name…') }}" aria-label="{{ __('Search name') }}">
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select" aria-label="{{ __('Status') }}">
                    <option value="">{{ __('Any status') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="stock" class="form-select" aria-label="{{ __('Stock') }}">
                    <option value="">{{ __('Any stock') }}</option>
                    <option value="in" @selected(request('stock') === 'in')>{{ __('In stock') }}</option>
                    <option value="out" @selected(request('stock') === 'out')>{{ __('Out of stock') }}</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button class="btn btn-outline-secondary" type="submit">
                    <x-icon name="search" /> {{ __('Filter') }}
                </button>
            </div>
        </div>
    </form>

    @if ($products->isEmpty())
        <x-empty-state :title="__('No products found')"
                       :message="__('Try adjusting your filters, or create your first product.')"
                       icon="product">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <x-icon name="plus" /> {{ __('Create your first product') }}
            </a>
        </x-empty-state>
    @else
        <x-table :headers="[__('Product'), __('SKU'), __('Price'), __('Stock'), __('Status'), __('Actions')]" sticky>
            @foreach ($products as $product)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if ($product->image_path)
                                <img src="{{ Storage::url($product->image_path) }}" alt="" width="40" height="40"
                                     class="rounded object-fit-cover">
                            @else
                                <span class="avatar-circle"><x-icon name="product" /></span>
                            @endif
                            <span class="fw-semibold">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="small text-muted">{{ $product->sku }}</td>
                    <td class="fw-medium">€{{ number_format((float) $product->price, 2) }}</td>
                    <td><x-stock-badge :product="$product" /></td>
                    <td><x-boolean-badge :value="$product->is_active" /></td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1 justify-content-end" role="group"
                             aria-label="{{ __('Product actions') }}">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="btn btn-sm btn-outline-secondary btn-icon" title="{{ __('Edit') }}"
                               aria-label="{{ __('Edit') }}">
                                <x-icon name="edit" />
                            </a>
                            <form method="POST" action="{{ route('admin.products.toggle', $product) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-secondary btn-icon"
                                        title="{{ $product->is_active ? __('Deactivate') : __('Activate') }}"
                                        aria-label="{{ $product->is_active ? __('Deactivate') : __('Activate') }}">
                                    <x-icon name="power" />
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                  onsubmit="return confirm('{{ __('Delete this product?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger btn-icon"
                                        title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}">
                                    <x-icon name="trash" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-3">
            {{ $products->links() }}
        </div>
    @endif
@endsection

