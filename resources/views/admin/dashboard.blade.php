<x-admin.layout title="Admin Dashboard">
    <x-admin.welcome-banner :user-name="auth()->user()->name" />

    <x-admin.dashboard-stats :counts="$counts" :trends="$trends" />

    <div class="adm-main-grid">
        <x-admin.recent-activities :activities="$recentActivities" />

        <x-admin.quick-administration />
    </div>
</x-admin.layout>
