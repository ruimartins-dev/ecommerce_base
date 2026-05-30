@extends('layouts.admin')

@section('title', __('Catalogs'))

@section('content')
    <x-admin.page-header
        :title="__('Catalogs')"
        :subtitle="__('Manage product catalogs and their availability windows.')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Catalogs')],
        ]">
        <x-slot:actions>
            <a href="{{ route('admin.catalogs.create') }}" class="btn btn-primary">
                {{ __('New catalog') }}
            </a>
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

    @if ($catalogs->isEmpty())
        <x-empty-state :message="__('No catalogs found.')" />
    @else
        <x-table :headers="[__('Name'), __('Status'), __('Products'), __('Window'), __('Actions')]">
            @foreach ($catalogs as $catalog)
                <tr>
                    <td>
                        <span class="fw-semibold">{{ $catalog->name }}</span>
                    </td>
                    <td><x-boolean-badge :value="$catalog->is_active" /></td>
                    <td>{{ $catalog->products_count }}</td>
                    <td class="small text-muted">
                        {{ optional($catalog->starts_at)->format('Y-m-d') ?? '—' }}
                        &rarr;
                        {{ optional($catalog->ends_at)->format('Y-m-d') ?? '—' }}
                    </td>
                    <td class="text-nowrap">
                        <a href="{{ route('admin.catalogs.products.edit', $catalog) }}" class="btn btn-sm btn-outline-secondary">
                            {{ __('Products') }}
                        </a>
                        <a href="{{ route('admin.catalogs.edit', $catalog) }}" class="btn btn-sm btn-outline-primary">
                            {{ __('Edit') }}
                        </a>
                        <form method="POST" action="{{ route('admin.catalogs.toggle', $catalog) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                {{ $catalog->is_active ? __('Deactivate') : __('Activate') }}
                            </button>
                        </form>
                        <x-delete-form :action="route('admin.catalogs.destroy', $catalog)"
                                       :confirm="__('Delete this catalog?')" />
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-3">
            {{ $catalogs->links() }}
        </div>
    @endif
@endsection

