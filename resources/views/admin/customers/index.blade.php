@extends('layouts.admin')

@section('title', __('Customers'))

@section('content')
    <x-admin.page-header
        :title="__('Customers')"
        :subtitle="__('Manage B2B customer accounts.')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Customers')],
        ]">
        <x-slot:actions>
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">{{ __('New customer') }}</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-6">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="{{ __('Search company, VAT, name or email…') }}">
        </div>
        <div class="col-8 col-md-4">
            <select name="status" class="form-select">
                <option value="">{{ __('All') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                <option value="blocked" @selected(request('status') === 'blocked')>{{ __('Blocked') }}</option>
            </select>
        </div>
        <div class="col-4 col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit">{{ __('Filter') }}</button>
        </div>
    </form>

    @if ($customers->isEmpty())
        <x-empty-state :message="__('No customers found.')" />
    @else
        <x-table :headers="[__('Company'), __('User'), __('VAT'), __('Addresses'), __('Orders'), __('Status'), __('Actions')]">
            @foreach ($customers as $customer)
                <tr>
                    <td class="fw-semibold">{{ $customer->company_name }}</td>
                    <td>
                        {{ $customer->user?->name ?? '—' }}
                        <span class="d-block small text-muted">{{ $customer->user?->email }}</span>
                    </td>
                    <td>{{ $customer->vat_number ?? '—' }}</td>
                    <td>{{ $customer->addresses_count }}</td>
                    <td>{{ $customer->orders_count }}</td>
                    <td>
                        <x-boolean-badge :value="! $customer->is_blocked" :true="__('Active')" :false="__('Blocked')" />
                    </td>
                    <td class="text-nowrap">
                        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary">
                            {{ __('Edit') }}
                        </a>
                        <form method="POST" action="{{ route('admin.customers.toggle-block', $customer) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $customer->is_blocked ? 'btn-outline-success' : 'btn-outline-danger' }}">
                                {{ $customer->is_blocked ? __('Unblock') : __('Block') }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-3">
            {{ $customers->links() }}
        </div>
    @endif
@endsection

