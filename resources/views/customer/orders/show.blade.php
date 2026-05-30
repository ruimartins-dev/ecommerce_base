@extends('layouts.customer')

@section('title', __('Order :number', ['number' => $order->order_number]))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('Account'), 'url' => route('customer.dashboard')],
        ['label' => __('My Orders'), 'url' => route('customer.orders.index')],
        ['label' => $order->order_number],
    ]" />

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-1">{{ __('Order :number', ['number' => $order->order_number]) }}</h1>
            <p class="text-muted mb-0">
                {{ optional($order->placed_at ?? $order->created_at)->format('Y-m-d H:i') }}
            </p>
        </div>
        <x-order-status-badge
            :status="$order->status"
            id="order-status-badge"
            :data-order-id="$order->id"
            :data-updated-message="__('Order status updated')" />
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h6 text-muted text-uppercase mb-3">{{ __('Items') }}</h2>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('SKU') }}</th>
                                    <th class="text-end">{{ __('Unit price') }}</th>
                                    <th class="text-end">{{ __('Qty') }}</th>
                                    <th class="text-end">{{ __('Line total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Snapshots only: never the live product values. --}}
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product_name_snapshot }}</td>
                                        <td class="small text-muted">{{ $item->sku_snapshot }}</td>
                                        <td class="text-end">€{{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td class="text-end">{{ $item->quantity }}</td>
                                        <td class="text-end">€{{ number_format((float) $item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">{{ __('Subtotal') }}</th>
                                    <td class="text-end">€{{ number_format((float) $order->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">{{ __('Discount') }}</th>
                                    <td class="text-end">€{{ number_format((float) $order->discount_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">{{ __('Tax') }}</th>
                                    <td class="text-end">€{{ number_format((float) $order->tax_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">{{ __('Total') }}</th>
                                    <td class="text-end fw-bold">€{{ number_format((float) $order->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h6 text-muted text-uppercase mb-3">{{ __('Shipping address') }}</h2>
                    @if ($order->address)
                        <address class="small mb-0">
                            <strong>{{ $order->address->recipient_name }}</strong><br>
                            @if ($order->address->company_name){{ $order->address->company_name }}<br>@endif
                            {{ $order->address->address_line_1 }}<br>
                            @if ($order->address->address_line_2){{ $order->address->address_line_2 }}<br>@endif
                            {{ $order->address->postal_code }} {{ $order->address->city }}<br>
                            {{ $order->address->country }}
                        </address>
                    @else
                        <p class="text-muted small mb-0">{{ __('No address on file.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary">
            {{ __('Back to orders') }}
        </a>
    </div>
@endsection

