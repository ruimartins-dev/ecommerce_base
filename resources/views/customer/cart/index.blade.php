@extends('layouts.customer')

@section('title', __('Cart'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('Account'), 'url' => route('customer.dashboard')],
        ['label' => __('Cart')],
    ]" />

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <h1 class="h3 mb-0">{{ __('Shopping cart') }}</h1>
        @if ($items->isNotEmpty())
            <form method="POST" action="{{ route('customer.cart.clear') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <x-icon name="trash" /> {{ __('Clear cart') }}
                </button>
            </form>
        @endif
    </div>

    @if ($items->isEmpty())
        <x-empty-state :title="__('Your cart is empty')"
                       :message="__('O carrinho está vazio')"
                       icon="cart">
            <a href="{{ route('customer.products.index') }}" class="btn btn-primary">
                <x-icon name="product" /> {{ __('Browse products') }}
            </a>
        </x-empty-state>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('SKU') }}</th>
                            <th class="text-end">{{ __('Unit price') }}</th>
                            <th style="width: 12rem;">{{ __('Quantity') }}</th>
                            <th class="text-end">{{ __('Subtotal') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($item->product->image_path)
                                            <img src="{{ Storage::url($item->product->image_path) }}" alt=""
                                                 width="48" height="48" class="rounded object-fit-cover">
                                        @endif
                                        <a href="{{ route('customer.products.show', $item->product) }}"
                                           class="fw-semibold text-decoration-none">{{ $item->product->name }}</a>
                                    </div>
                                </td>
                                <td class="small text-muted">{{ $item->product->sku }}</td>
                                <td class="text-end">€{{ number_format($item->unitPrice(), 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('customer.cart.update', $item->product) }}"
                                          class="d-flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                               max="{{ $item->product->stock }}" class="form-control form-control-sm"
                                               style="width: 5rem;">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            {{ __('Update') }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">€{{ number_format($item->subtotal(), 2) }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('customer.cart.remove', $item->product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            {{ __('Remove') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">{{ __('Total') }}</th>
                            <td class="text-end fw-bold">€{{ number_format($total, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mt-3 d-flex flex-wrap gap-2">
            <a href="{{ route('customer.products.index') }}" class="btn btn-outline-secondary">
                <x-icon name="arrow-left" /> {{ __('Continue shopping') }}
            </a>
            <a href="{{ route('customer.checkout.index') }}" class="btn btn-primary ms-md-auto">
                {{ __('Proceed to checkout') }} <x-icon name="check" />
            </a>
        </div>
    @endif
@endsection

