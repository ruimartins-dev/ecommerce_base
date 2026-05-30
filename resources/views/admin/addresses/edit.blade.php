@extends('layouts.admin')

@section('title', __('Edit address'))

@section('content')
    <x-admin.page-header
        :title="__('Edit address')"
        :subtitle="$address->recipient_name"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Addresses'), 'url' => route('admin.addresses.index')],
            ['label' => __('Edit')],
        ]" />

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.addresses.update', $address) }}">
                @csrf
                @method('PUT')
                @include('admin.addresses._form')

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                    <a href="{{ route('admin.addresses.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

