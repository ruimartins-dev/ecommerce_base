@extends('layouts.admin')

@section('title', __('New catalog'))

@section('content')
    <x-admin.page-header
        :title="__('New catalog')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Catalogs'), 'url' => route('admin.catalogs.index')],
            ['label' => __('New')],
        ]" />

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.catalogs.store') }}">
                @csrf
                @include('admin.catalogs._form')

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Create catalog') }}</button>
                    <a href="{{ route('admin.catalogs.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

