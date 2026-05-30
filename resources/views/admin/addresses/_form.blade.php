{{--
    Shared address form fields. Expects:
      $address   – the (possibly new) Address model.
      $customers – collection of customers selectable as owner.
--}}
<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="customer_id" class="form-label">{{ __('Customer') }} <span class="text-danger">*</span></label>
        <select id="customer_id" name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
            <option value="">{{ __('— Select a customer —') }}</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected((int) old('customer_id', $address->customer_id) === $customer->id)>
                    {{ $customer->company_name }} ({{ $customer->user?->email }})
                </option>
            @endforeach
        </select>
        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="label" class="form-label">{{ __('Label') }}</label>
        <input type="text" id="label" name="label" class="form-control @error('label') is-invalid @enderror"
               value="{{ old('label', $address->label) }}" placeholder="{{ __('e.g. Warehouse') }}">
        @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="recipient_name" class="form-label">{{ __('Recipient name') }} <span class="text-danger">*</span></label>
        <input type="text" id="recipient_name" name="recipient_name"
               class="form-control @error('recipient_name') is-invalid @enderror"
               value="{{ old('recipient_name', $address->recipient_name) }}" required>
        @error('recipient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="company_name" class="form-label">{{ __('Company name') }}</label>
        <input type="text" id="company_name" name="company_name"
               class="form-control @error('company_name') is-invalid @enderror"
               value="{{ old('company_name', $address->company_name) }}">
        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="address_line_1" class="form-label">{{ __('Address line 1') }} <span class="text-danger">*</span></label>
        <input type="text" id="address_line_1" name="address_line_1"
               class="form-control @error('address_line_1') is-invalid @enderror"
               value="{{ old('address_line_1', $address->address_line_1) }}" required>
        @error('address_line_1')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="address_line_2" class="form-label">{{ __('Address line 2') }}</label>
        <input type="text" id="address_line_2" name="address_line_2"
               class="form-control @error('address_line_2') is-invalid @enderror"
               value="{{ old('address_line_2', $address->address_line_2) }}">
        @error('address_line_2')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-4">
        <label for="postal_code" class="form-label">{{ __('Postal code') }} <span class="text-danger">*</span></label>
        <input type="text" id="postal_code" name="postal_code"
               class="form-control @error('postal_code') is-invalid @enderror"
               value="{{ old('postal_code', $address->postal_code) }}" required>
        @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-4">
        <label for="city" class="form-label">{{ __('City') }} <span class="text-danger">*</span></label>
        <input type="text" id="city" name="city" class="form-control @error('city') is-invalid @enderror"
               value="{{ old('city', $address->city) }}" required>
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="country" class="form-label">{{ __('Country') }} <span class="text-danger">*</span></label>
        <input type="text" id="country" name="country" class="form-control @error('country') is-invalid @enderror"
               value="{{ old('country', $address->country) }}" required>
        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="vat_number" class="form-label">{{ __('VAT number') }}</label>
        <input type="text" id="vat_number" name="vat_number"
               class="form-control @error('vat_number') is-invalid @enderror"
               value="{{ old('vat_number', $address->vat_number) }}">
        @error('vat_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="is_default" value="0">
            <input type="checkbox" id="is_default" name="is_default" value="1" class="form-check-input"
                   @checked(old('is_default', $address->is_default))>
            <label for="is_default" class="form-check-label">{{ __('Default address for this customer') }}</label>
        </div>
    </div>
</div>

