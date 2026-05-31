@extends('layouts.customer')

@section('title', __('My Account'))

@section('content')
    @php($user = auth()->user())
    @php($customer = $user->customer)

    <x-breadcrumbs :items="[
        ['label' => __('Account'), 'url' => route('customer.dashboard')],
        ['label' => __('Dashboard')],
    ]" />

    <div class="mb-4">
        <h1 class="h3 mb-1">{{ __('My Account') }}</h1>
        <p class="text-muted mb-0">{{ __('Welcome back, :name.', ['name' => $user->name]) }}</p>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="section-label mb-3">{{ __('Account details') }}</h2>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">{{ __('Name') }}</dt>
                        <dd class="col-7 fw-medium">{{ $user->name }}</dd>

                        <dt class="col-5 text-muted fw-normal">{{ __('Email') }}</dt>
                        <dd class="col-7">{{ $user->email }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="section-label mb-3">{{ __('Company') }}</h2>
                    @if ($customer)
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted fw-normal">{{ __('Company') }}</dt>
                            <dd class="col-7 fw-medium">{{ $customer->company_name }}</dd>

                            <dt class="col-5 text-muted fw-normal">{{ __('VAT number') }}</dt>
                            <dd class="col-7">{{ $customer->vat_number ?? '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">{{ __('Phone') }}</dt>
                            <dd class="col-7">{{ $customer->phone ?? '—' }}</dd>
                        </dl>
                    @else
                        <p class="text-muted small mb-0">
                            {{ __('No company profile is linked to this account yet.') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <h2 class="section-label mb-0">{{ __('Recent orders') }}</h2>
                        <a href="{{ route('customer.orders.index') }}" class="small text-decoration-none">
                            {{ __('View all') }}
                        </a>
                    </div>

                    @if ($recentOrders->isEmpty())
                        <p class="text-muted small mb-0">{{ __('You have not placed any orders yet.') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Order') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-end">{{ __('Total') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentOrders as $order)
                                        <tr>
                                            <td class="fw-semibold">{{ $order->order_number }}</td>
                                            <td class="small text-muted">
                                                {{ optional($order->placed_at ?? $order->created_at)->format('Y-m-d') }}
                                            </td>
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
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

