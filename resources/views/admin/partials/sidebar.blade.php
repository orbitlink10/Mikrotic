@php
    $showSidebarBrand = $showSidebarBrand ?? true;
    $activeAdminNav = $activeAdminNav ?? 'dashboard';
    $adminNavItems = [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'badge' => 'DB', 'href' => route('admin.dashboard')],
        ['id' => 'pages-content', 'label' => 'Homepage Content', 'badge' => 'HC', 'href' => route('admin.pages-content.edit')],
        ['id' => 'categories', 'label' => 'Categories', 'badge' => 'CT', 'href' => route('admin.categories.index')],
        ['id' => 'subcategories', 'label' => 'Sub Categories', 'badge' => 'SC', 'href' => route('admin.subcategories.index')],
        ['id' => 'products', 'label' => 'Products', 'badge' => 'PR', 'href' => route('admin.products.index')],
        ['id' => 'pages', 'label' => 'Pages', 'badge' => 'PG', 'href' => route('admin.pages.index')],
        ['id' => 'orders', 'label' => 'Orders', 'badge' => 'OR', 'href' => route('admin.orders.index')],
        ['id' => 'invoices', 'label' => 'Invoices', 'badge' => 'IV', 'href' => route('admin.invoices.index')],
    ];

    $heroItem = $adminNavItems[0];
    $menuItems = array_slice($adminNavItems, 1);
@endphp

<aside class="admin-sidebar">
    @if($showSidebarBrand)
        <div class="admin-brand-card">
            <h2>{{ config('app.name', 'Almar Market') }}</h2>
        </div>
    @endif

    <a class="admin-nav-link admin-nav-link--hero @if($activeAdminNav === $heroItem['id']) is-active @endif" href="{{ $heroItem['href'] }}">
        <span class="admin-nav-badge">{{ $heroItem['badge'] }}</span>
        <span>{{ $heroItem['label'] }}</span>
    </a>

    <div class="admin-sidebar-scroll">
        <div class="admin-sidebar-menu">
            <p class="admin-sidebar-label">Content Management</p>
            <nav class="admin-sidebar-nav admin-sidebar-nav--menu">
                @foreach($menuItems as $item)
                    <a class="admin-nav-link admin-nav-link--menu @if($activeAdminNav === $item['id']) is-active @endif" href="{{ $item['href'] }}">
                        <span class="admin-nav-badge">{{ $item['badge'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
    </div>
</aside>
