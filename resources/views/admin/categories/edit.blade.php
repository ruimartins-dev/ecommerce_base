@extends('layouts.admin')

@section('title', __('Edit category'))

@section('content')
    <x-admin.page-header
        :title="__('Edit category')"
        :subtitle="$category->name"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Categories'), 'url' => route('admin.categories.index')],
            ['label' => __('Edit')],
        ]" />

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                @csrf
                @method('PUT')
                @include('admin.categories._form')

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

