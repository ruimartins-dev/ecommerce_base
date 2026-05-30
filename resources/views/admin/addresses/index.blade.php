@extends('layouts.admin')

@section('title', __('Addresses'))

@section('content')
    <x-admin.page-header
        :title="__('Addresses')"
        :subtitle="__('Manage customer shipping and billing addresses.')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Addresses')],
        ]">
        <x-slot:actions>
            <a href="{{ route('admin.addresses.create') }}" class="btn btn-primary">{{ __('New address') }}</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-5">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="{{ __('Search recipient, city or postal code…') }}">
        </div>
        <div class="col-8 col-md-5">
            <select name="customer" class="form-select">
                <option value="">{{ __('All customers') }}</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((int) request('customer') === $customer->id)>
                        {{ $customer->company_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit">{{ __('Filter') }}</button>
        </div>
    </form>

    @if ($addresses->isEmpty())
        <x-empty-state :message="__('No addresses found.')" />
    @else
        <x-table :headers="[__('Recipient'), __('Customer'), __('Location'), __('Default'), __('Actions')]">
            @foreach ($addresses as $address)
                <tr>
                    <td>
                        <span class="fw-semibold">{{ $address->recipient_name }}</span>
                        <span class="d-block small text-muted">{{ $address->address_line_1 }}</span>
                    </td>
                    <td>{{ $address->customer?->company_name ?? '—' }}</td>
                    <td class="small">
                        {{ $address->postal_code }} {{ $address->city }}
                        <span class="d-block text-muted">{{ $address->country }}</span>
                    </td>
                    <td>
                        @if ($address->is_default)
                            <span class="badge text-bg-primary">{{ __('Default') }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-nowrap">
                        <a href="{{ route('admin.addresses.edit', $address) }}" class="btn btn-sm btn-outline-primary">
                            {{ __('Edit') }}
                        </a>
                        <x-delete-form :action="route('admin.addresses.destroy', $address)"
                                       :confirm="__('Delete this address?')" />
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-3">
            {{ $addresses->links() }}
        </div>
    @endif
@endsection

