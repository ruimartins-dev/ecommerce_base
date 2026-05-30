@extends('layouts.admin')

@section('title', __('New category'))

@section('content')
    <x-admin.page-header
        :title="__('New category')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Categories'), 'url' => route('admin.categories.index')],
            ['label' => __('New')],
        ]" />

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                @include('admin.categories._form')

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Create category') }}</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

