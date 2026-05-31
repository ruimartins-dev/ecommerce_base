@extends('layouts.admin')

@section('title', __('Orders'))

@section('content')
    <x-admin.page-header
        :title="__('Orders')"
        :subtitle="__('Review orders and manage their status.')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Orders')],
        ]" />

    <form method="GET" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="{{ __('Search by order number…') }}" aria-label="{{ __('Search orders') }}">
            </div>
            <div class="col-8 col-md-4">
                <select name="status" class="form-select" aria-label="{{ __('Status') }}">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-md-2 d-grid">
                <button class="btn btn-outline-secondary" type="submit">
                    <x-icon name="search" /> {{ __('Filter') }}
                </button>
            </div>
        </div>
    </form>

    @if ($orders->isEmpty())
        <x-empty-state :title="__('No orders found')"
                       :message="__('Orders placed by customers will appear here.')"
                       icon="order" />
    @else
        <x-table :headers="[__('Order'), __('Customer'), __('Total'), __('Status'), __('Placed'), __('Actions')]" sticky>
            @foreach ($orders as $order)
                <tr>
                    <td class="fw-semibold">{{ $order->order_number }}</td>
                    <td>
                        {{ $order->customer?->company_name ?? '—' }}
                        <span class="d-block small text-muted">{{ $order->customer?->user?->email }}</span>
                    </td>
                    <td class="fw-medium">€{{ number_format((float) $order->total, 2) }}</td>
                    <td><x-order-status-badge :status="$order->status" /></td>
                    <td class="small text-muted">{{ optional($order->placed_at)->format('Y-m-d') ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                            <x-icon name="eye" /> {{ __('View') }}
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-3">
            {{ $orders->links() }}
        </div>
    @endif
@endsection

