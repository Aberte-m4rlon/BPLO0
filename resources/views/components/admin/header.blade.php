@props([
    'workspaceKicker' => 'ADMINISTRATION WORKSPACE',
    'title' => 'Dashboard',
    'homeUrl' => '/',
    'unread' => 0,
    'user' => null,
    'roleLabel' => '',
])

<header class="topbar adm-topbar">
    {{-- Mobile Brand Pill (<= 767px matching reference) --}}
    <a href="{{ url($homeUrl) }}" class="adm-mobile-brand" aria-label="{{ config('permit.municipality') }}">
        <span class="sidebar-brand-mark adm-mobile-mark" aria-hidden="true">BP</span>
        <span class="adm-mobile-brand-name">{{ config('permit.municipality') }}</span>
    </a>

    {{-- Desktop / Tablet Left Identity --}}
    <span class="topbar-brand-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="9" r="2.2" />
            <circle cx="15" cy="9" r="2.2" />
            <circle cx="9" cy="15" r="2.2" />
            <circle cx="15" cy="15" r="2.2" />
        </svg>
    </span>

    <div class="topbar-heading">
        <span class="topbar-kicker">{{ $workspaceKicker }}</span>
        <span class="topbar-title">{{ $title }}</span>
    </div>

    <div class="topbar-right">
        <x-notification-bell :count="$unread" />
        @if ($user)
            <div class="topbar-user">
                <span class="topbar-avatar-wrap">
                    <span class="topbar-avatar" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <span class="topbar-avatar-status" aria-hidden="true"></span>
                </span>
                <span class="topbar-user-meta">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ $roleLabel }}</small>
                </span>
                <svg class="adm-user-chevron" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </div>
        @endif

        <button type="button"
                id="mobile-drawer-open"
                class="hamburger adm-mobile-menu-btn"
                title="Open navigation"
                aria-label="Open navigation"
                aria-controls="mobile-navigation-drawer"
                aria-expanded="false">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
    </div>
</header>
