@extends('layouts.customer')

@section('title', __('Checkout'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('Account'), 'url' => route('customer.dashboard')],
        ['label' => __('Cart'), 'url' => route('customer.cart.index')],
        ['label' => __('Checkout')],
    ]" />

    <div class="mb-4">
        <h1 class="h3 mb-1">{{ __('Checkout') }}</h1>
        <p class="text-muted mb-0">{{ __('Review your order, choose a delivery address and confirm.') }}</p>
    </div>

    <x-validation-errors />

    <form method="POST" action="{{ route('customer.checkout.store') }}" id="checkout-form">
        @csrf

        <div class="row g-4">
            {{-- Left: address selection + order summary --}}
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="h6 text-muted text-uppercase mb-3">{{ __('Delivery address') }}</h2>

                        @if ($addresses === null || $addresses->isEmpty())
                            <x-empty-state :message="__('Não tem endereços disponíveis.')" />
                        @else
                            <div class="row g-3">
                                @foreach ($addresses as $address)
                                    @php($checked = old('address_id', $defaultAddressId) == $address->id)
                                    <div class="col-12 col-md-6">
                                        <label class="card h-100 w-100 border @if ($checked) border-primary @endif"
                                               style="cursor: pointer;">
                                            <div class="card-body">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="address_id"
                                                           value="{{ $address->id }}"
                                                           id="address-{{ $address->id }}"
                                                           @checked($checked)>
                                                    <span class="form-check-label fw-semibold">
                                                        {{ $address->label }}
                                                        @if ($address->is_default)
                                                            <span class="badge text-bg-secondary ms-1">{{ __('Default') }}</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <address class="small text-muted mb-0">
                                                    <strong>{{ $address->recipient_name }}</strong><br>
                                                    @if ($address->company_name){{ $address->company_name }}<br>@endif
                                                    {{ $address->address_line_1 }}<br>
                                                    @if ($address->address_line_2){{ $address->address_line_2 }}<br>@endif
                                                    {{ $address->postal_code }} {{ $address->city }}<br>
                                                    {{ $address->country }}
                                                </address>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('address_id')
                                <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 text-muted text-uppercase mb-3">{{ __('Order summary') }}</h2>
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
                                    @foreach ($items as $item)
                                        <tr>
                                            <td class="fw-semibold">{{ $item->product->name }}</td>
                                            <td class="small text-muted">{{ $item->product->sku }}</td>
                                            <td class="text-end">€{{ number_format($item->unitPrice(), 2) }}</td>
                                            <td class="text-end">{{ $item->quantity }}</td>
                                            <td class="text-end">€{{ number_format($item->subtotal(), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: totals + confirm --}}
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm position-lg-sticky" style="top: 1rem;">
                    <div class="card-body">
                        <h2 class="h6 text-muted text-uppercase mb-3">{{ __('Totals') }}</h2>
                        <dl class="row mb-0">
                            <dt class="col-7 fw-normal">{{ __('Subtotal') }}</dt>
                            <dd class="col-5 text-end">€{{ number_format($subtotal, 2) }}</dd>

                            <dt class="col-7 fw-normal">{{ __('Discount') }}</dt>
                            <dd class="col-5 text-end">€{{ number_format(0, 2) }}</dd>

                            <dt class="col-7 fw-normal">{{ __('Tax') }}</dt>
                            <dd class="col-5 text-end">€{{ number_format(0, 2) }}</dd>

                            <dt class="col-7 border-top pt-2 fs-5">{{ __('Total') }}</dt>
                            <dd class="col-5 border-top pt-2 text-end fs-5 fw-bold">€{{ number_format($subtotal, 2) }}</dd>
                        </dl>

                        <button type="submit" class="btn btn-primary w-100 mt-3" id="checkout-submit"
                                @disabled($addresses === null || $addresses->isEmpty())>
                            {{ __('Confirm order') }}
                        </button>

                        <a href="{{ route('customer.cart.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                            {{ __('Back to cart') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Basic double-submit protection: disable the button on first submit. --}}
    <script>
        document.getElementById('checkout-form')?.addEventListener('submit', function () {
            const button = document.getElementById('checkout-submit');
            if (button) {
                button.disabled = true;
                button.textContent = @json(__('A processar...'));
            }
        });
    </script>
@endsection

