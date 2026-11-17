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
<body class="bplo-portal bplo-guest">
    @php
        $isStaffAuth = request()->is('login', 'forgot-password', 'reset-password*');
    @endphp
    <header class="bplo-guest-header">
        <a href="{{ url('/') }}" class="bplo-guest-brand">
            <span class="sidebar-brand-mark">BP</span>
            <span>{{ config('permit.municipality') }}<small>Business Permits and Licensing Office</small></span>
        </a>
        <span class="bplo-guest-secure"><span aria-hidden="true"></span> {{ $isStaffAuth ? 'Secure staff portal' : 'Official municipal portal' }}</span>
    </header>

    <main class="page">
        <div class="container bplo-guest-content">
            <x-flash-messages />
            @yield('content')
        </div>
    </main>

    <footer class="bplo-guest-footer">
        &copy; {{ date('Y') }} {{ config('permit.municipality') }}, {{ config('permit.province') }}
        <span>{{ $isStaffAuth ? 'Official BPLO personnel access' : 'Automated Business Permit & Verification System' }}</span>
    </footer>
    <script>
        document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-target'));
                if (!input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                btn.setAttribute('aria-pressed', String(show));
            });
        });
    </script>
</body>
</html>
