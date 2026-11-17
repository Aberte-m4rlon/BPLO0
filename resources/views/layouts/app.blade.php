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
<body class="has-sidebar bplo-portal">
    @php
        $user = auth()->user();
        $unread = $user ? \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count() : 0;

        // Role-aware navigation links: [url, label].
        $nav = [];
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
                ['/admin/fee-types', 'Fee Types'],
                ['/admin/fee-rates', 'Fee Rates'],
                ['/admin/audit-logs', 'Audit Logs'],
                ['/admin/settings', 'Settings'],
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
                    @php $active = $current === $url || str_starts_with($current, rtrim($url, '/').'/'); @endphp
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
                    <a href="{{ url($url) }}" class="sidebar-link {{ $active ? 'is-active' : '' }}" @if ($active) aria-current="page" @endif>
                        <svg class="sidebar-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="{{ $iconPath }}" />
                        </svg>
                        <span>{{ $label }}</span>
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
</body>
</html>
