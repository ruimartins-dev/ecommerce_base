@extends('layouts.admin')

@section('title', __('New customer'))

@section('content')
    <x-admin.page-header
        :title="__('New customer')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Customers'), 'url' => route('admin.customers.index')],
            ['label' => __('New')],
        ]" />

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.customers.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="user_id" class="form-label">{{ __('Linked user') }} <span class="text-danger">*</span></label>
                        <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">{{ __('— Select a user —') }}</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((int) old('user_id') === $user->id)>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if ($users->isEmpty())
                            <div class="form-text text-warning">
                                {{ __('No eligible users available. Customer-role users without a profile are required.') }}
                            </div>
                        @endif
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="company_name" class="form-label">{{ __('Company name') }} <span class="text-danger">*</span></label>
                        <input type="text" id="company_name" name="company_name"
                               class="form-control @error('company_name') is-invalid @enderror"
                               value="{{ old('company_name') }}" required>
                        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="vat_number" class="form-label">{{ __('VAT number') }}</label>
                        <input type="text" id="vat_number" name="vat_number"
                               class="form-control @error('vat_number') is-invalid @enderror"
                               value="{{ old('vat_number') }}">
                        @error('vat_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="phone" class="form-label">{{ __('Phone') }}</label>
                        <input type="text" id="phone" name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="is_blocked" value="0">
                            <input type="checkbox" id="is_blocked" name="is_blocked" value="1" class="form-check-input"
                                   @checked(old('is_blocked'))>
                            <label for="is_blocked" class="form-check-label">{{ __('Blocked') }}</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Create customer') }}</button>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

