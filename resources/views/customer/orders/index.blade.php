@extends('layouts.customer')

@section('title', __('My Orders'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('Account'), 'url' => route('customer.dashboard')],
        ['label' => __('My Orders')],
    ]" />

    <div class="mb-4">
        <h1 class="h3 mb-1">{{ __('My Orders') }}</h1>
        <p class="text-muted mb-0">{{ __('Review your order history and status.') }}</p>
    </div>

    @if ($orders->isEmpty())
        <x-empty-state :message="__('You have not placed any orders yet.')">
            <a href="{{ route('customer.products.index') }}" class="btn btn-primary">
                {{ __('Browse products') }}
            </a>
        </x-empty-state>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Order') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Items') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Total') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="fw-semibold">{{ $order->order_number }}</td>
                                <td class="small text-muted">
                                    {{ optional($order->placed_at ?? $order->created_at)->format('Y-m-d') }}
                                </td>
                                <td>{{ $order->items_count }}</td>
                                <td><x-order-status-badge :status="$order->status" /></td>
                                <td class="text-end">€{{ number_format((float) $order->total, 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('customer.orders.show', $order) }}"
                                       class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
@endsection

