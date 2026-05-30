@extends('layouts.admin')

@section('title', __('Edit product'))

@section('content')
    <x-admin.page-header
        :title="__('Edit product')"
        :subtitle="$product->name"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Products'), 'url' => route('admin.products.index')],
            ['label' => __('Edit')],
        ]" />

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.products._form')

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

