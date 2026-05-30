{{--
    Shared category form fields. Expects:
      $category – the (possibly new) Category model.
      $parents  – collection of categories selectable as parent.
--}}
<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="parent_id" class="form-label">{{ __('Parent category') }}</label>
        <select id="parent_id" name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
            <option value="">{{ __('— None (top level) —') }}</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((int) old('parent_id', $category->parent_id) === $parent->id)>
                    {{ $parent->name }}
                </option>
            @endforeach
        </select>
        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="sort_order" class="form-label">{{ __('Sort order') }}</label>
        <input type="number" id="sort_order" name="sort_order" min="0" class="form-control @error('sort_order') is-invalid @enderror"
               value="{{ old('sort_order', $category->sort_order ?? 0) }}">
        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $category->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="slug" class="form-label">{{ __('Slug') }}</label>
        <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror"
               value="{{ old('slug', $category->slug) }}" placeholder="{{ __('Auto-generated from name if left blank') }}">
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="3"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                   @checked(old('is_active', $category->is_active ?? true))>
            <label for="is_active" class="form-check-label">{{ __('Active') }}</label>
        </div>
    </div>
</div>

