<nav class="sidebar-nav">
    <div class="nav-section-label">Overview</div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.dashboard') }}" class="nav-link {{ $isActive('admin.dashboard', 'admin.dashboard.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg></span>
            <span class="nav-label">Dashboard</span>
        </a>
    </div>

    <div class="nav-section-label">Catalog</div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.products.index') }}" class="nav-link {{ $isActive('admin.products.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96 12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg></span>
            <span class="nav-label">Products</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.categories.index') }}" class="nav-link {{ $isActive('admin.categories.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2z"/></svg></span>
            <span class="nav-label">Categories</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.collections.index') }}" class="nav-link {{ $isActive('admin.collections.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg></span>
            <span class="nav-label">Collections</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.inventory.index') }}" class="nav-link {{ $isActive('admin.inventory.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><path d="M12 12v4"/><path d="M10 14h4"/></svg></span>
            <span class="nav-label">Inventory</span>
        </a>
    </div>

    <div class="nav-section-label">Sales</div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.orders.index') }}" class="nav-link {{ $isActive('admin.orders.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M6 2h12l2 7H4L6 2z"/><path d="M4 9h16v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9z"/><path d="M9 13h6"/></svg></span>
            <span class="nav-label">Orders</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.customers.index') }}" class="nav-link {{ $isActive('admin.customers.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
            <span class="nav-label">Customers</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.returns.index') }}" class="nav-link {{ $isActive('admin.returns.*', 'admin.refunds.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg></span>
            <span class="nav-label">Returns &amp; Refunds</span>
        </a>
    </div>

    <div class="nav-section-label">Marketing</div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.coupons.index') }}" class="nav-link {{ $isActive('admin.coupons.*', 'admin.offers.*', 'admin.flash-sales.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><circle cx="7" cy="7" r="1.5"/></svg></span>
            <span class="nav-label">Offers &amp; Coupons</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.reviews.index') }}" class="nav-link {{ $isActive('admin.reviews.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></span>
            <span class="nav-label">Reviews</span>
        </a>
    </div>

    <div class="nav-section-label">Store</div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.banners.index') }}" class="nav-link {{ $isActive('admin.banners.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></span>
            <span class="nav-label">Banners</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.store-locations.index') }}" class="nav-link {{ $isActive('admin.store-locations.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
            <span class="nav-label">Store Locations</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.enquiries.index') }}" class="nav-link {{ $isActive('admin.enquiries.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg></span>
            <span class="nav-label">Enquiries</span>
        </a>
    </div>

    <div class="nav-section-label">Insights</div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.reports.index') }}" class="nav-link {{ $isActive('admin.reports.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l4-4 4 2 5-6"/></svg></span>
            <span class="nav-label">Reports</span>
        </a>
    </div>

    <div class="nav-section-label">Content</div>

    <div class="nav-item {{ $isActive('admin.pages.*', 'admin.blogs.*', 'admin.faqs.*', 'admin.testimonials.*', 'admin.menus.*', 'admin.homepage-sections.*') ? 'open' : '' }}">
        <button type="button" class="nav-link {{ $isActive('admin.pages.*', 'admin.blogs.*', 'admin.faqs.*', 'admin.testimonials.*', 'admin.menus.*', 'admin.homepage-sections.*') ? 'active' : '' }}" data-nav-toggle>
            <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg></span>
            <span class="nav-label">Website Content</span>
            <svg class="nav-chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
        </button>
        <div class="nav-submenu">
            <a href="{{ $adminRoute('admin.pages.index') }}" class="nav-sublink {{ $isActive('admin.pages.*') ? 'active' : '' }}">Pages</a>
            <a href="{{ $adminRoute('admin.blogs.index') }}" class="nav-sublink {{ $isActive('admin.blogs.*') ? 'active' : '' }}">Blogs</a>
            <a href="{{ $adminRoute('admin.faqs.index') }}" class="nav-sublink {{ $isActive('admin.faqs.*') ? 'active' : '' }}">FAQs</a>
            <a href="{{ $adminRoute('admin.testimonials.index') }}" class="nav-sublink {{ $isActive('admin.testimonials.*') ? 'active' : '' }}">Testimonials</a>
            <a href="{{ $adminRoute('admin.menus.index') }}" class="nav-sublink {{ $isActive('admin.menus.*') ? 'active' : '' }}">Menus</a>
            <a href="{{ $adminRoute('admin.homepage-sections.index') }}" class="nav-sublink {{ $isActive('admin.homepage-sections.*') ? 'active' : '' }}">Homepage Sections</a>
        </div>
    </div>

    <div class="nav-section-label">System</div>

    <div class="nav-item">
        <a href="{{ $adminRoute('admin.settings.index') }}" class="nav-link {{ $isActive('admin.settings.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span>
            <span class="nav-label">Settings</span>
        </a>
    </div>
</nav>
