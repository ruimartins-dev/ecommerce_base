@extends('layouts.admin')

@section('title', __('Categories'))

@section('content')
    <x-admin.page-header
        :title="__('Categories')"
        :subtitle="__('Organise products into a hierarchy of categories.')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Categories')],
        ]">
        <x-slot:actions>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">{{ __('New category') }}</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-6">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="{{ __('Search by name…') }}">
        </div>
        <div class="col-8 col-md-4">
            <select name="status" class="form-select">
                <option value="">{{ __('All statuses') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
            </select>
        </div>
        <div class="col-4 col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit">{{ __('Filter') }}</button>
        </div>
    </form>

    @if ($categories->isEmpty())
        <x-empty-state :message="__('No categories found.')" />
    @else
        <x-table :headers="[__('Name'), __('Parent'), __('Status'), __('Order'), __('Children'), __('Products'), __('Actions')]">
            @foreach ($categories as $category)
                <tr>
                    <td>
                        @if ($category->parent)
                            <span class="text-muted">&rsaquo;</span>
                        @endif
                        <span class="fw-semibold">{{ $category->name }}</span>
                        <span class="d-block small text-muted">{{ $category->slug }}</span>
                    </td>
                    <td>{{ $category->parent?->name ?? '—' }}</td>
                    <td><x-boolean-badge :value="$category->is_active" /></td>
                    <td>{{ $category->sort_order }}</td>
                    <td>{{ $category->children_count }}</td>
                    <td>{{ $category->products_count }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">
                            {{ __('Edit') }}
                        </a>
                        <x-delete-form :action="route('admin.categories.destroy', $category)"
                                       :confirm="__('Delete this category?')" />
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-3">
            {{ $categories->links() }}
        </div>
    @endif
@endsection

