@extends('layouts.admin')

@section('title', __('New product'))

@section('content')
    <x-admin.page-header
        :title="__('New product')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Products'), 'url' => route('admin.products.index')],
            ['label' => __('New')],
        ]" />

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.products._form')

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Create product') }}</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

