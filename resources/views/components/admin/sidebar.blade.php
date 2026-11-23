@props([
    'nav' => [],
    'homeUrl' => '/',
    'user' => null,
    'roleLabel' => '',
    'current' => '/',
    'badges' => [],
])

<aside class="sidebar adm-sidebar" id="desktop-sidebar" aria-label="Primary sidebar">
    <div class="sidebar-brand">
        <a href="{{ url($homeUrl) }}" class="adm-sidebar-brand-link" title="{{ config('permit.municipality') }}">
            <span class="sidebar-brand-mark" aria-hidden="true">BP</span>
            <span class="sidebar-brand-text">
                {{ config('permit.municipality') }}
                <small>Business Permits Office</small>
            </span>
        </a>
        <button type="button"
                id="sidebar-collapse-btn"
                class="adm-sidebar-collapse-btn"
                title="Collapse sidebar"
                aria-label="Toggle sidebar width"
                aria-expanded="true"
                aria-controls="desktop-sidebar">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m15 18-6-6 6-6" />
            </svg>
        </button>
    </div>

    <nav id="primary-navigation" class="sidebar-nav" aria-label="Primary navigation">
        <p class="sidebar-section-label">WORKSPACE</p>
        @foreach ($nav as [$url, $label, $icon])
            @php
                $active = $current === $url || str_starts_with($current, rtrim($url, '/').'/');
                $badge = $badges[$url] ?? null;
                $badgeCount = (int) ($badge['count'] ?? 0);
                $hasDot = ! empty($badge['dot']);
                $tone = $badge['tone'] ?? 'info';
                $badgeTitle = $badge['title'] ?? $label;
            @endphp
            <a href="{{ url($url) }}"
               class="sidebar-link {{ $active ? 'is-active' : '' }}"
               data-tooltip="{{ $label }}"
               title="{{ $badge ? $label.' — '.$badgeTitle : $label }}"
               @if ($active) aria-current="page" @endif>
                <span class="nav-icon-wrap">
                    <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="{{ $icon }}" />
                    </svg>
                    @if ($badge && ($badgeCount > 0 || $hasDot))
                        <span class="nav-icon-badge {{ $badgeCount > 0 ? 'has-num' : 'is-dot' }} tone-{{ $tone }}" aria-hidden="true">{{ $badgeCount > 0 ? ($badgeCount > 99 ? '99+' : $badgeCount) : '' }}</span>
                    @endif
                </span>
                <span class="sidebar-link-label">{{ $label }}</span>
                @if ($badge && $badgeCount > 0)
                    <span class="nav-badge tone-{{ $tone }}" title="{{ $badgeTitle }}">{{ $badgeCount > 99 ? '99+' : $badgeCount }}</span>
                @elseif ($badge && $hasDot)
                    <span class="nav-dot tone-{{ $tone }}" title="{{ $badgeTitle }}"></span>
                @endif
            </a>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        @if ($user)
            <div class="sidebar-user" title="{{ $user->name }} — {{ $roleLabel }}">
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
            <button type="submit" class="btn btn-sm btn-secondary btn-block adm-logout-btn" title="Logout" data-tooltip="Logout">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9" />
                </svg>
                <span class="adm-logout-label">Logout</span>
            </button>
        </form>
    </div>
</aside>
