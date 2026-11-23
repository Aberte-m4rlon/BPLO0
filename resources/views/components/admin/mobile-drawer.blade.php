@props([
    'nav' => [],
    'homeUrl' => '/',
    'user' => null,
    'roleLabel' => '',
    'current' => '/',
])

<div id="mobile-drawer-backdrop" class="adm-drawer-backdrop" hidden></div>

<aside id="mobile-navigation-drawer"
       class="adm-mobile-drawer"
       aria-label="Mobile navigation"
       aria-hidden="true">
    <div class="adm-drawer-header">
        <a href="{{ url($homeUrl) }}" class="adm-drawer-brand">
            <span class="sidebar-brand-mark" aria-hidden="true">BP</span>
            <span class="sidebar-brand-text">
                {{ config('permit.municipality') }}
                <small>Business Permits Office</small>
            </span>
        </a>
        <button type="button"
                id="mobile-drawer-close"
                class="adm-drawer-close"
                aria-label="Close navigation menu">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M18 6 6 18 M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="adm-drawer-nav" aria-label="Mobile workspace links">
        <p class="sidebar-section-label">WORKSPACE</p>
        @foreach ($nav as [$url, $label, $icon])
            @php $active = $current === $url || str_starts_with($current, rtrim($url, '/').'/'); @endphp
            <a href="{{ url($url) }}"
               class="sidebar-link adm-drawer-link {{ $active ? 'is-active' : '' }}"
               @if ($active) aria-current="page" @endif>
                <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="{{ $icon }}" />
                </svg>
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-footer adm-drawer-footer">
        @if ($user)
            <div class="sidebar-user">
                <span class="adm-avatar-status-wrap">
                    <span class="sidebar-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <span class="topbar-avatar-status" aria-hidden="true"></span>
                </span>
                <span class="sidebar-user-meta">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ $roleLabel }}</small>
                </span>
            </div>
        @endif
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-secondary btn-block">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9" />
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>
