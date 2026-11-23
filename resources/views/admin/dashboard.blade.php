@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="page-header bplo-page-header bplo-dashboard-hero">
        <div class="bplo-hero-copy">
            <span class="bplo-hero-pill">ADMINISTRATION WORKSPACE</span>
            <h1>Good day, <span class="bplo-hero-highlight">{{ auth()->user()->name }}</span>!</h1>
            <p>Monitor system users, permit activity, fees, and municipal audit logs.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ url('/admin/users') }}" class="btn bplo-hero-primary">Manage Users</a>
            <a href="{{ url('/admin/audit-logs') }}" class="btn bplo-hero-secondary">Audit Logs</a>
        </div>
    </div>

    <div class="stat-grid stat-grid-6">
        <x-stat-card :value="$counts['users']" label="Total Users" icon="👥" accent="mint" />
        <x-stat-card :value="$counts['applications']" label="Total Applications" icon="📋" accent="default" />
        <x-stat-card :value="$counts['permits']" label="Total Permits" icon="📄" accent="default" />
        <x-stat-card :value="$counts['payments']" label="Total Payments" icon="💰" accent="green" />
        <x-stat-card :value="$counts['active_permits']" label="Active Permits" icon="✅" accent="green" />
        <x-stat-card :value="$counts['expired_permits']" label="Expired Permits" icon="⚠️" accent="amber" />
    </div>

    <div class="bplo-split-grid">
        <div class="card">
            <div class="card-head">
                <h2 class="section">Recent Activities</h2>
                <a href="{{ url('/admin/audit-logs') }}" class="btn btn-sm btn-secondary">All Logs →</a>
            </div>
            @if ($recentActivities->isEmpty())
                <x-empty-state icon="📜" title="No activity yet"
                    message="Logins, approvals, payments, and other actions will show up here." />
            @else
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr><th>When</th><th>Actor</th><th>Action</th><th>Subject</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($recentActivities as $log)
                                <tr>
                                    <td>{{ $log->created_at?->format('M d, Y g:i A') }}</td>
                                    <td>{{ $log->actor?->name ?? 'System' }}</td>
                                    <td><span class="badge badge-blue">{{ ucwords(strtolower(str_replace(['.', '_'], ' ', $log->action))) }}</span></td>
                                    <td>{{ class_basename($log->subject_type ?? '') }} {{ $log->subject_id }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card bplo-sticky-card">
            <div class="card-head">
                <h2 class="section">Quick Administration</h2>
            </div>
            <div class="bplo-admin-links">
                <a href="{{ url('/admin/users') }}" class="bplo-admin-link-tile">
                    <strong>Users &amp; Roles</strong>
                    <small>Accounts, roles &amp; access</small>
                </a>
                <a href="{{ url('/admin/requirement-types') }}" class="bplo-admin-link-tile">
                    <strong>Requirements</strong>
                    <small>Permit upload checklist</small>
                </a>
                <a href="{{ url('/admin/fee-types') }}" class="bplo-admin-link-tile">
                    <strong>Fee Types</strong>
                    <small>Assessment categories</small>
                </a>
                <a href="{{ url('/admin/fee-rates') }}" class="bplo-admin-link-tile">
                    <strong>Fee Rates</strong>
                    <small>Active peso schedules</small>
                </a>
                <a href="{{ url('/admin/audit-logs') }}" class="bplo-admin-link-tile">
                    <strong>Audit Logs</strong>
                    <small>System activity trail</small>
                </a>
                <a href="{{ url('/admin/settings') }}" class="bplo-admin-link-tile">
                    <strong>System Settings</strong>
                    <small>Environment configuration</small>
                </a>
            </div>
        </div>
    </div>
@endsection
