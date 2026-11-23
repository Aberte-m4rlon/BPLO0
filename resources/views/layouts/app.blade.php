<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('permit.municipality').' — Business Permit System')</title>
    <meta name="theme-color" content="#2684D9">
    <link rel="preload" as="image" href="{{ asset('images/bplo-blue-background.png') }}" fetchpriority="high">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
</head>
<body class="has-sidebar bplo-portal {{ auth()->user()?->isAdmin() ? 'is-glass-portal is-admin-portal' : (auth()->user()?->isTreasurer() ? 'is-glass-portal is-treasurer-portal' : '') }}">
    @php
        $user = auth()->user();
        $alertData = app(\App\Support\NavigationAlertService::class)->forUser($user);
        $unread = $alertData['bell_count'];
        $navBadges = $alertData['badges'];
        $notifItems = $alertData['bell_items'];
        $unreadDbCount = $alertData['unread_db_count'];

        // Role-aware navigation links: [url, label].
        $nav = [];
        $glassNav = [];
        $glassBottomItems = null;
        if ($user?->isApplicant()) {
            $nav = [
                ['/dashboard', 'Dashboard'],
                ['/applications/create', 'New Application'],
            ];
        } elseif ($user?->isBplo()) {
            $nav = [
                ['/bplo/dashboard', 'Dashboard'],
                ['/bplo/applications', 'Application Review'],
                ['/bplo/permits', 'Permit Release'],
            ];
        } elseif ($user?->isTreasurer()) {
            $nav = [
                ['/treasurer/dashboard', 'Dashboard'],
                ['/treasurer/payments', 'Payments'],
                ['/treasurer/analytics', 'Analytics'],
                ['/treasurer/fee-types', 'Fee Types'],
                ['/treasurer/fee-rates', 'Fee Rates'],
            ];
            $glassNav = [
                ['/treasurer/dashboard', 'Dashboard', 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5.5v-6h-5v6H4a1 1 0 0 1-1-1z'],
                ['/treasurer/payments', 'Payments', 'M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1 M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4'],
                ['/treasurer/analytics', 'Analytics', 'M3 3v18h18 M18 17V9 M13 17V5 M8 17v-3'],
                ['/treasurer/fee-types', 'Fee Types', 'M12 2v20 M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
                ['/treasurer/fee-rates', 'Fee Rates', 'M4 2h16v20H4z M8 6h8 M8 10h2 M12 10h2 M16 10h0 M8 14h2 M12 14h2 M16 14h0 M8 18h6'],
            ];
            $currentPath = '/'.ltrim(request()->path(), '/');
            $glassBottomItems = [
                [
                    'url' => '/treasurer/dashboard',
                    'label' => 'Dashboard',
                    'active' => $currentPath === '/treasurer/dashboard',
                    'icon' => 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5.5v-6h-5v6H4a1 1 0 0 1-1-1z',
                ],
                [
                    'url' => '/treasurer/payments',
                    'label' => 'Payments',
                    'active' => str_starts_with($currentPath, '/treasurer/payments'),
                    'icon' => 'M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1 M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4',
                ],
                [
                    'url' => '/treasurer/analytics',
                    'label' => 'Analytics',
                    'active' => str_starts_with($currentPath, '/treasurer/analytics'),
                    'icon' => 'M3 3v18h18 M18 17V9 M13 17V5 M8 17v-3',
                ],
            ];
        } elseif ($user?->isInspector()) {
            $nav = [
                ['/inspector/dashboard', 'Dashboard'],
                ['/inspector/permits', 'Permit Monitoring'],
                ['/verify', 'QR Verification'],
            ];
        } elseif ($user?->isAdmin()) {
            $nav = [
                ['/admin/dashboard', 'Dashboard'],
                ['/admin/users', 'Users'],
                ['/admin/requirement-types', 'Requirements'],
                ['/admin/audit-logs', 'Audit Logs'],
                ['/admin/settings', 'Settings'],
            ];
            $glassNav = [
                ['/admin/dashboard', 'Dashboard', 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5.5v-6h-5v6H4a1 1 0 0 1-1-1z'],
                ['/admin/users', 'Users', 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M22 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75'],
                ['/admin/requirement-types', 'Requirements', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M9 15l2 2 4-4'],
                ['/admin/audit-logs', 'Audit Logs', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M8 13h8 M8 17h8 M8 9h2'],
                ['/admin/settings', 'Settings', 'M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z'],
            ];
        }

        $roleLabel = $user ? match ($user->role) {
            'bplo' => 'BPLO Municipal Personnel',
            'inspector' => 'Municipal Permit Inspector',
            'treasurer' => 'Municipal Treasurer',
            'admin' => 'System Administrator',
            'applicant' => 'Business Owner / Applicant',
            default => ucfirst($user->role),
        } : '';
        $workspaceKicker = $user ? match ($user->role) {
            'bplo' => 'BPLO STAFF WORKSPACE',
            'inspector' => 'INSPECTOR WORKSPACE',
            'treasurer' => 'TREASURY WORKSPACE',
            'admin' => 'ADMINISTRATION WORKSPACE',
            'applicant' => 'APPLICANT PORTAL',
            default => 'MUNICIPAL WORKSPACE',
        } : 'MUNICIPAL WORKSPACE';
        $homeUrl = $user ? $user->homePath() : '/';
        $current = '/'.ltrim(request()->path(), '/');
    @endphp

    @if ($user?->isAdmin() || $user?->isTreasurer())
        @php
            $admCurrent = '/'.ltrim(request()->path(), '/');
            $admHomeUrl = $user->homePath();
            $admRoleLabel = $roleLabel;
            $admWorkspaceKicker = $workspaceKicker;
            $admPageTitle = trim($__env->yieldContent('title', $user->isTreasurer() ? 'Treasurer Dashboard' : 'Admin Dashboard')) ?: ($user->isTreasurer() ? 'Treasurer Dashboard' : 'Admin Dashboard');
        @endphp

        <script>
            if (localStorage.getItem('bplo_sidebar_collapsed') === '1') {
                document.body.classList.add('is-sidebar-collapsed');
            }
        </script>

        <x-admin.mobile-drawer
            :nav="$glassNav"
            :home-url="$admHomeUrl"
            :user="$user"
            :role-label="$admRoleLabel"
            :current="$admCurrent"
            :badges="$navBadges"
        />

        <div class="layout adm-layout">
            <x-admin.sidebar
                :nav="$glassNav"
                :home-url="$admHomeUrl"
                :user="$user"
                :role-label="$admRoleLabel"
                :current="$admCurrent"
                :badges="$navBadges"
            />

            <div class="main-area adm-main-area">
                <x-admin.header
                    :workspace-kicker="$admWorkspaceKicker"
                    :title="$admPageTitle"
                    :home-url="$admHomeUrl"
                    :unread="$unread"
                    :notif-items="$notifItems"
                    :unread-db-count="$unreadDbCount"
                    :user="$user"
                    :role-label="$admRoleLabel"
                />

                <main class="page adm-page">
                    <div class="container-fluid adm-container">
                        <x-flash-messages />
                        @yield('content')
                    </div>
                </main>

                <x-admin.footer />
            </div>
        </div>

        <x-admin.mobile-bottom-nav :current="$admCurrent" :items="$glassBottomItems" :badges="$navBadges" />

        <script>
            (function () {
                var collapseBtn = document.getElementById('sidebar-collapse-btn');
                var drawerOpenBtn = document.getElementById('mobile-drawer-open');
                var drawerCloseBtn = document.getElementById('mobile-drawer-close');
                var drawerBackdrop = document.getElementById('mobile-drawer-backdrop');
                var drawer = document.getElementById('mobile-navigation-drawer');
                var clockEl = document.getElementById('adm-live-clock');

                function syncCollapseButton() {
                    if (!collapseBtn) return;
                    var collapsed = document.body.classList.contains('is-sidebar-collapsed');
                    collapseBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                    collapseBtn.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
                }

                if (collapseBtn) {
                    syncCollapseButton();
                    collapseBtn.addEventListener('click', function () {
                        var collapsed = document.body.classList.toggle('is-sidebar-collapsed');
                        localStorage.setItem('bplo_sidebar_collapsed', collapsed ? '1' : '0');
                        syncCollapseButton();
                    });
                }

                function setDrawerOpen(open) {
                    if (!drawer || !drawerBackdrop) return;
                    drawer.classList.toggle('is-open', open);
                    drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
                    drawerBackdrop.hidden = !open;
                    drawerBackdrop.classList.toggle('is-open', open);
                    document.documentElement.classList.toggle('drawer-open', open);
                    document.body.classList.toggle('drawer-open', open);
                    if (drawerOpenBtn) {
                        drawerOpenBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                    }
                    if (open && drawerCloseBtn) {
                        drawerCloseBtn.focus();
                    }
                }

                if (drawerBackdrop) {
                    drawerBackdrop.addEventListener('wheel', function (event) {
                        event.preventDefault();
                    }, { passive: false });
                    drawerBackdrop.addEventListener('touchmove', function (event) {
                        event.preventDefault();
                    }, { passive: false });
                }

                if (drawerOpenBtn) {
                    drawerOpenBtn.addEventListener('click', function () {
                        setDrawerOpen(true);
                    });
                }

                if (drawerCloseBtn) {
                    drawerCloseBtn.addEventListener('click', function () {
                        setDrawerOpen(false);
                    });
                }

                if (drawerBackdrop) {
                    drawerBackdrop.addEventListener('click', function () {
                        setDrawerOpen(false);
                    });
                }

                if (drawer) {
                    drawer.querySelectorAll('.adm-drawer-link').forEach(function (link) {
                        link.addEventListener('click', function () {
                            setDrawerOpen(false);
                        });
                    });
                }

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && drawer && drawer.classList.contains('is-open')) {
                        setDrawerOpen(false);
                    }
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth > 767 && drawer && drawer.classList.contains('is-open')) {
                        setDrawerOpen(false);
                    }
                });

                if (clockEl) {
                    var updateClock = function () {
                        var now = new Date();
                        clockEl.dateTime = now.toISOString();
                        clockEl.textContent = now.toLocaleString('en-US', {
                            month: 'short',
                            day: '2-digit',
                            year: 'numeric',
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                    };
                    updateClock();
                    setInterval(updateClock, 30000);
                }
            })();
        </script>
    @else
        <input type="checkbox" id="sidebar-toggle" class="sidebar-toggle-cb" hidden>

        <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <a href="{{ url($homeUrl) }}">
                    <span class="sidebar-brand-mark">BP</span>
                    <span class="sidebar-brand-text">
                        {{ config('permit.municipality') }}
                        <small>Business Permits Office</small>
                    </span>
                </a>
            </div>

            <nav id="primary-navigation" class="sidebar-nav" aria-label="Primary navigation">
                <p class="sidebar-section-label">WORKSPACE</p>
                @foreach ($nav as [$url, $label])
                    @php
                        $active = $current === $url || str_starts_with($current, rtrim($url, '/').'/');
                        $badge = $navBadges[$url] ?? null;
                        $badgeCount = (int) ($badge['count'] ?? 0);
                        $hasDot = ! empty($badge['dot']);
                        $tone = $badge['tone'] ?? 'info';
                        $badgeTitle = $badge['title'] ?? $label;
                    @endphp
                    @php
                        $iconPath = match (true) {
                            str_contains($url, 'dashboard') => 'M3 3.75h7.5v7.5H3z M13.5 3.75H21v4.5h-7.5z M13.5 10.5H21V21h-7.5z M3 13.5h7.5V21H3z',
                            str_contains($url, 'application') => 'M6 3.75h8.5L19 8.25v12H6z M14 4v4.5h4.5 M9 12h6 M9 15.5h6 M9 8.75h1',
                            str_contains($url, 'permit') => 'M5 4.5h14v15H5z M8 8h8 M8 11.5h5 M8 15h8 M15.5 14.5l1.5 1.5 3-3',
                            str_contains($url, 'payment') => 'M3.5 6.5h17v12h-17z M3.5 10h17 M7 14.5h4',
                            str_contains($url, 'requirement') => 'M6 3.75h12v16.5H6z M9 8h6 M9 12h6 M9 16h4',
                            str_contains($url, 'fee') => 'M12 3.5v17 M16.5 7H9.75a3.25 3.25 0 0 0 0 6.5h4.5a3.25 3.25 0 0 1 0 6.5H7.5',
                            str_contains($url, 'audit') => 'M5 4.5h14v15H5z M8 8h8 M8 11.5h8 M8 15h5',
                            str_contains($url, 'user') => 'M12 12a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z M5 20a7 7 0 0 1 14 0',
                            str_contains($url, 'verify') => 'M4 4h6v6H4z M14 4h6v6h-6z M4 14h6v6H4z M15 14h2v2h-2z M18 17h2v3h-2z',
                            str_contains($url, 'settings') => 'M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7z M19.4 15a1.8 1.8 0 0 0 .36 1.99l.07.07-1.7 2.94-.1-.03a1.8 1.8 0 0 0-1.93.62l-.06.08h-3.4l-.03-.1a1.8 1.8 0 0 0-1.5-1.3l-.1-.02-1.7-2.94.07-.08a1.8 1.8 0 0 0 .36-1.99l-.04-.1 1.7-2.94.1.03a1.8 1.8 0 0 0 1.93-.62l.06-.08h3.4l.03.1a1.8 1.8 0 0 0 1.5 1.3l.1.02 1.7 2.94-.07.08a1.8 1.8 0 0 0-.36 1.99z',
                            default => 'M5 6h14 M5 12h14 M5 18h14',
                        };
                    @endphp
                    <a href="{{ url($url) }}" class="sidebar-link {{ $active ? 'is-active' : '' }}" title="{{ $badge ? $label.' — '.$badgeTitle : $label }}" @if ($active) aria-current="page" @endif>
                        <span class="nav-icon-wrap">
                            <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="{{ $iconPath }}" />
                            </svg>
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
                <div class="sidebar-user">
                    <span class="sidebar-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <span class="sidebar-user-meta">
                        <strong>{{ $user->name }}</strong>
                        <small>{{ $roleLabel }}</small>
                    </span>
                </div>
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

        {{-- Dim backdrop that closes the sidebar on mobile --}}
        <button type="button" class="sidebar-backdrop" aria-label="Close navigation"></button>

        <div class="main-area">
            <header class="topbar">
                <button type="button" id="sidebar-menu-button" class="hamburger" title="Open navigation" aria-label="Open navigation" aria-controls="primary-navigation" aria-expanded="false">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <span class="topbar-brand-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M23 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </span>
                <div class="topbar-heading">
                    <span class="topbar-kicker">{{ $workspaceKicker }}</span>
                    <span class="topbar-title">@yield('title', 'Dashboard')</span>
                </div>
                <div class="topbar-right">
                    <x-notification-bell :count="$unread" :items="$notifItems" :unread-db-count="$unreadDbCount" />
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
                        </div>
                    @endif
                </div>
            </header>

            <main class="page">
                <div class="container-fluid">
                    <x-flash-messages />
                    @yield('content')
                </div>
            </main>

            <footer class="footer">
                &copy; {{ date('Y') }} {{ config('permit.municipality') }}, {{ config('permit.province') }}.
                Automated Business Permit Application and Status Tracking System.
            </footer>
        </div>
    </div>

    <script>
        (function () {
            var sidebarToggle = document.getElementById('sidebar-toggle');
            var sidebarButton = document.getElementById('sidebar-menu-button');
            var sidebarBackdrop = document.querySelector('.sidebar-backdrop');

            function syncSidebarState() {
                if (sidebarButton && sidebarToggle) {
                    sidebarButton.setAttribute('aria-expanded', String(sidebarToggle.checked));
                    sidebarButton.setAttribute('aria-label', sidebarToggle.checked ? 'Close navigation' : 'Open navigation');
                }
            }

            if (sidebarButton && sidebarToggle) {
                sidebarButton.addEventListener('click', function () {
                    sidebarToggle.checked = !sidebarToggle.checked;
                    syncSidebarState();
                });

                sidebarToggle.addEventListener('change', syncSidebarState);
            }

            if (sidebarBackdrop && sidebarToggle) {
                sidebarBackdrop.addEventListener('click', function () {
                    sidebarToggle.checked = false;
                    syncSidebarState();
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && sidebarToggle && sidebarToggle.checked) {
                    sidebarToggle.checked = false;
                    syncSidebarState();
                }
            });

            document.querySelectorAll('.sidebar-link').forEach(function (a) {
                a.addEventListener('click', function () {
                    if (sidebarToggle) sidebarToggle.checked = false;
                    syncSidebarState();
                });
            });
        })();
    </script>
    @endif

    <script>
        (function () {
            document.querySelectorAll('[data-notif-wrap]').forEach(function (wrap) {
                var btn = wrap.querySelector('[data-notif-toggle]');
                var popover = wrap.querySelector('[data-notif-popover]');
                if (!btn || !popover) return;

                function setOpen(open) {
                    popover.hidden = !open;
                    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                    wrap.classList.toggle('is-open', open);
                }

                btn.addEventListener('click', function (event) {
                    event.stopPropagation();
                    var willOpen = popover.hidden;
                    document.querySelectorAll('[data-notif-wrap]').forEach(function (other) {
                        if (other !== wrap) {
                            var p = other.querySelector('[data-notif-popover]');
                            var b = other.querySelector('[data-notif-toggle]');
                            if (p) p.hidden = true;
                            if (b) b.setAttribute('aria-expanded', 'false');
                            other.classList.remove('is-open');
                        }
                    });
                    setOpen(willOpen);
                });

                popover.addEventListener('click', function (event) {
                    event.stopPropagation();
                });
            });

            document.addEventListener('click', function () {
                document.querySelectorAll('[data-notif-wrap]').forEach(function (wrap) {
                    var p = wrap.querySelector('[data-notif-popover]');
                    var b = wrap.querySelector('[data-notif-toggle]');
                    if (p) p.hidden = true;
                    if (b) b.setAttribute('aria-expanded', 'false');
                    wrap.classList.remove('is-open');
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('[data-notif-wrap]').forEach(function (wrap) {
                        var p = wrap.querySelector('[data-notif-popover]');
                        var b = wrap.querySelector('[data-notif-toggle]');
                        if (p) p.hidden = true;
                        if (b) b.setAttribute('aria-expanded', 'false');
                        wrap.classList.remove('is-open');
                    });
                }
            });
        })();
    </script>
</body>
</html>
