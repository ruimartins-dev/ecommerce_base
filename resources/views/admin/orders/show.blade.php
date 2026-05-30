@extends('layouts.admin')

@section('title', __('Order :number', ['number' => $order->order_number]))

@section('content')
    <x-admin.page-header
        :title="__('Order :number', ['number' => $order->order_number])"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Orders'), 'url' => route('admin.orders.index')],
            ['label' => $order->order_number],
        ]">
        <x-slot:actions>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">{{ __('Back to orders') }}</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0 mb-3">
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
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product_name_snapshot }}</td>
                                        <td class="small text-muted">{{ $item->sku_snapshot }}</td>
                                        <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td class="text-end">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ number_format((float) $item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">{{ __('Subtotal') }}</th>
                                    <td class="text-end">{{ number_format((float) $order->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">{{ __('Discount') }}</th>
                                    <td class="text-end">{{ number_format((float) $order->discount_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">{{ __('Tax') }}</th>
                                    <td class="text-end">{{ number_format((float) $order->tax_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">{{ __('Total') }}</th>
                                    <td class="text-end fw-bold">{{ number_format((float) $order->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h2 class="h6 text-muted text-uppercase mb-3">{{ __('Status') }}</h2>
                    <p class="mb-3"><x-order-status-badge :status="$order->status" /></p>

                    @if (empty($allowedStatuses))
                        <p class="text-muted small mb-0">{{ __('This order is closed and cannot change status.') }}</p>
                    @else
                        <form method="POST" action="{{ route('admin.orders.status.update', $order) }}">
                            @csrf
                            @method('PATCH')
                            <label for="status" class="form-label">{{ __('Move to') }}</label>
                            <select id="status" name="status" class="form-select mb-3">
                                @foreach ($allowedStatuses as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary w-100">{{ __('Update status') }}</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h2 class="h6 text-muted text-uppercase mb-3">{{ __('Customer') }}</h2>
                    <dl class="row mb-0 small">
                        <dt class="col-5">{{ __('Company') }}</dt>
                        <dd class="col-7">{{ $order->customer?->company_name ?? '—' }}</dd>
                        <dt class="col-5">{{ __('Contact') }}</dt>
                        <dd class="col-7">{{ $order->customer?->user?->name ?? '—' }}</dd>
                        <dt class="col-5">{{ __('Email') }}</dt>
                        <dd class="col-7">{{ $order->customer?->user?->email ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

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
@endsection

