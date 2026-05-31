@php
    // Grouped navigation model — single source of truth for the admin sidebar.
    // Each item: label, route name (for active state), href, icon.
    $groups = [
        [
            'heading' => null,
            'items' => [
                ['label' => __('Dashboard'), 'pattern' => 'admin.dashboard', 'href' => route('admin.dashboard'), 'icon' => 'dashboard'],
            ],
        ],
        [
            'heading' => __('Catalog'),
            'items' => [
                ['label' => __('Catalogs'), 'pattern' => 'admin.catalogs.*', 'href' => route('admin.catalogs.index'), 'icon' => 'catalog'],
                ['label' => __('Categories'), 'pattern' => 'admin.categories.*', 'href' => route('admin.categories.index'), 'icon' => 'category'],
                ['label' => __('Products'), 'pattern' => 'admin.products.*', 'href' => route('admin.products.index'), 'icon' => 'product'],
            ],
        ],
        [
            'heading' => __('Sales'),
            'items' => [
                ['label' => __('Orders'), 'pattern' => 'admin.orders.*', 'href' => route('admin.orders.index'), 'icon' => 'order'],
                ['label' => __('Customers'), 'pattern' => 'admin.customers.*', 'href' => route('admin.customers.index'), 'icon' => 'customer'],
                ['label' => __('Addresses'), 'pattern' => 'admin.addresses.*', 'href' => route('admin.addresses.index'), 'icon' => 'address'],
            ],
        ],
        [
            'heading' => __('System'),
            'items' => [
                ['label' => __('Audit logs'), 'pattern' => 'admin.audit-logs.*', 'href' => route('admin.audit-logs.index'), 'icon' => 'audit'],
            ],
        ],
    ];
@endphp

{{-- Offcanvas on mobile (<lg), static column on desktop (≥lg). --}}
<aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar"
       aria-label="{{ __('Admin navigation') }}">
    <div class="sidebar-brand">
        <span class="brand-mark">{{ strtoupper(substr(config('app.name', 'B'), 0, 1)) }}</span>
        <span class="text-truncate">{{ config('app.name', 'B2B Ministore') }}</span>
        <button type="button" class="btn-close ms-auto d-lg-none" data-bs-dismiss="offcanvas"
                data-bs-target="#adminSidebar" aria-label="{{ __('Close') }}"></button>
    </div>

    <nav class="sidebar-nav">
        @foreach ($groups as $group)
            @if ($group['heading'])
                <div class="sidebar-heading">{{ $group['heading'] }}</div>
            @endif
            @foreach ($group['items'] as $item)
                <a href="{{ $item['href'] }}"
                   class="sidebar-link {{ request()->routeIs($item['pattern']) ? 'active' : '' }}"
                   @if (request()->routeIs($item['pattern'])) aria-current="page" @endif>
                    <x-icon :name="$item['icon']" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        @endforeach
    </nav>
</aside>

