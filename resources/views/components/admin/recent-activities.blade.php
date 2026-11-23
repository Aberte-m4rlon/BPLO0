@props([
    'activities',
])

<section class="card adm-panel adm-activities-panel" aria-label="Recent Activities">
    <div class="card-head adm-panel-head">
        <div class="adm-panel-title-wrap">
            <span class="adm-panel-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                </svg>
            </span>
            <h2 class="adm-panel-title">Recent Activities</h2>
        </div>
        <a href="{{ url('/admin/audit-logs') }}" class="btn btn-sm adm-pill-btn">All Logs →</a>
    </div>

    @if ($activities->isEmpty())
        <x-empty-state icon="📜" title="No activity yet"
            message="Logins, approvals, payments, and other actions will show up here." />
    @else
        <x-admin.activity-table :activities="$activities" />

        <div class="adm-activity-mobile-list" aria-label="Recent activities list">
            @foreach ($activities as $log)
                <x-admin.activity-mobile-card :log="$log" />
            @endforeach
        </div>
    @endif
</section>
