{{-- Sticky admin topbar: mobile menu toggle, brand on mobile, user menu. --}}
<header class="app-topbar">
    <button class="btn btn-light border btn-icon d-lg-none" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#adminSidebar"
            aria-controls="adminSidebar" aria-label="{{ __('Open navigation') }}">
        <x-icon name="menu" />
    </button>

    <a href="{{ route('admin.dashboard') }}" class="fw-bold text-body d-lg-none">
        {{ config('app.name', 'B2B Ministore') }}
    </a>

    <span class="badge badge-soft-primary d-none d-lg-inline-flex">{{ __('Admin') }}</span>

    <div class="ms-auto d-flex align-items-center gap-2">
        <x-user-menu />
    </div>
</header>

