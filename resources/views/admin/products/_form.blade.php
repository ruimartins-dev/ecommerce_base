{{--
    Shared product form fields. Expects:
      $product             – the (possibly new) Product model.
      $categories          – collection of all categories.
      $catalogs            – collection of all catalogs.
      $selectedCategories  – array of currently assigned category ids.
      $selectedCatalogs    – array of currently assigned catalog ids.
--}}
<div class="row g-3">
    <div class="col-12 col-md-4">
        <label for="sku" class="form-label">{{ __('SKU') }} <span class="text-danger">*</span></label>
        <input type="text" id="sku" name="sku" class="form-control @error('sku') is-invalid @enderror"
               value="{{ old('sku', $product->sku) }}" required>
        @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $product->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="slug" class="form-label">{{ __('Slug') }}</label>
        <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror"
               value="{{ old('slug', $product->slug) }}" placeholder="{{ __('Auto-generated if left blank') }}">
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
        <textarea id="description" name="description" rows="3"
                  class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $product->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="price" class="form-label">{{ __('Price') }} <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="price" name="price"
               class="form-control @error('price') is-invalid @enderror"
               value="{{ old('price', $product->price) }}" required>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-4">
        <label for="stock" class="form-label">{{ __('Stock') }} <span class="text-danger">*</span></label>
        <input type="number" min="0" id="stock" name="stock"
               class="form-control @error('stock') is-invalid @enderror"
               value="{{ old('stock', $product->stock ?? 0) }}" required>
        @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-4">
        <label for="low_stock_threshold" class="form-label">{{ __('Low stock threshold') }} <span class="text-danger">*</span></label>
        <input type="number" min="0" id="low_stock_threshold" name="low_stock_threshold"
               class="form-control @error('low_stock_threshold') is-invalid @enderror"
               value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" required>
        @error('low_stock_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="image" class="form-label">{{ __('Image') }}</label>
        <input type="file" id="image" name="image" accept="image/*"
               class="form-control @error('image') is-invalid @enderror">
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if ($product->image_path)
            <div class="mt-2">
                <img src="{{ Storage::url($product->image_path) }}" alt="" width="80" height="80"
                     class="rounded object-fit-cover">
            </div>
        @endif
    </div>

    <div class="col-12 col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                   @checked(old('is_active', $product->is_active ?? true))>
            <label for="is_active" class="form-check-label">{{ __('Active') }}</label>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <label for="categories" class="form-label">{{ __('Categories') }}</label>
        <select id="categories" name="categories[]" multiple size="6"
                class="form-select @error('categories') is-invalid @enderror">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    @selected(in_array($category->id, old('categories', $selectedCategories)))>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('categories')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">{{ __('Hold Ctrl/Cmd to select multiple.') }}</div>
    </div>

    <div class="col-12 col-md-6">
        <label for="catalogs" class="form-label">{{ __('Catalogs') }}</label>
        <select id="catalogs" name="catalogs[]" multiple size="6"
                class="form-select @error('catalogs') is-invalid @enderror">
            @foreach ($catalogs as $catalog)
                <option value="{{ $catalog->id }}"
                    @selected(in_array($catalog->id, old('catalogs', $selectedCatalogs)))>
                    {{ $catalog->name }}
                </option>
            @endforeach
        </select>
        @error('catalogs')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">{{ __('Hold Ctrl/Cmd to select multiple.') }}</div>
    </div>
</div>

