@props([
    'userName' => 'Admin User',
])

<section class="page-header bplo-page-header bplo-dashboard-hero adm-hero" aria-label="Welcome banner">
    <div class="bplo-hero-copy adm-hero-copy">
        <span class="bplo-hero-pill adm-hero-pill">
            <span class="adm-hero-pill-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z M9 12l2 2 4-4" />
                </svg>
            </span>
            ADMINISTRATION WORKSPACE
        </span>
        <h1>Good day, <span class="adm-hero-highlight">{{ $userName }}!</span></h1>
        <p>Monitor system users, permit activity, fees, and municipal audit logs.</p>
        <div class="bplo-header-actions adm-hero-actions">
            <a href="{{ url('/admin/users') }}" class="btn adm-hero-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M22 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                Manage Users
            </a>
            <a href="{{ url('/admin/audit-logs') }}" class="btn adm-hero-btn-glass">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8 M3 3v5h5 M12 7v5l3 2" />
                </svg>
                Audit Logs
            </a>
        </div>
    </div>

    <div class="adm-hero-watermark" aria-hidden="true">
        <span class="adm-watermark-title">Bansud</span>
        <span class="adm-watermark-sub">Progress Together</span>
    </div>
</section>
