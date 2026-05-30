@extends('layouts.admin')

@section('title', __('Edit catalog'))

@section('content')
    <x-admin.page-header
        :title="__('Edit catalog')"
        :subtitle="$catalog->name"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Catalogs'), 'url' => route('admin.catalogs.index')],
            ['label' => __('Edit')],
        ]" />

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.catalogs.update', $catalog) }}">
                @csrf
                @method('PUT')
                @include('admin.catalogs._form')

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                    <a href="{{ route('admin.catalogs.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

