@extends('layouts.admin')

@section('title', __('New address'))

@section('content')
    <x-admin.page-header
        :title="__('New address')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Addresses'), 'url' => route('admin.addresses.index')],
            ['label' => __('New')],
        ]" />

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.addresses.store') }}">
                @csrf
                @include('admin.addresses._form')

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Create address') }}</button>
                    <a href="{{ route('admin.addresses.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

