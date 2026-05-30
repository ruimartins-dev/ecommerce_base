{{--
    Shared catalog form fields. Expects:
      $catalog – the (possibly new) Catalog model used to repopulate values.
--}}
<div class="row g-3">
    <div class="col-12">
        <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $catalog->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="3"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $catalog->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="starts_at" class="form-label">{{ __('Starts at') }}</label>
        <input type="date" id="starts_at" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror"
               value="{{ old('starts_at', optional($catalog->starts_at)->format('Y-m-d')) }}">
        @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="ends_at" class="form-label">{{ __('Ends at') }}</label>
        <input type="date" id="ends_at" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror"
               value="{{ old('ends_at', optional($catalog->ends_at)->format('Y-m-d')) }}">
        @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                   @checked(old('is_active', $catalog->is_active ?? true))>
            <label for="is_active" class="form-check-label">{{ __('Active') }}</label>
        </div>
    </div>
</div>

