@props([
    'counts' => [],
    'trends' => [],
])

<section class="adm-stats-grid" aria-label="Dashboard statistics">
    <x-admin.stat-card
        :value="$counts['users'] ?? 0"
        label="Total Users"
        icon="users"
        tone="blue"
        :trend="$trends['users'] ?? '+0%'"
    />
    <x-admin.stat-card
        :value="$counts['applications'] ?? 0"
        label="Total Application"
        icon="file-text"
        tone="purple"
        :trend="$trends['applications'] ?? '+0%'"
    />
    <x-admin.stat-card
        :value="$counts['permits'] ?? 0"
        label="Total Permits"
        icon="clipboard-check"
        tone="green"
        :trend="$trends['permits'] ?? '+0%'"
    />
    <x-admin.stat-card
        :value="$counts['payments'] ?? 0"
        label="Total Payments"
        icon="credit-card"
        tone="amber"
        :trend="$trends['payments'] ?? '+0%'"
    />
    <x-admin.stat-card
        :value="$counts['expired_permits'] ?? 0"
        label="Expired Permits"
        icon="alert-triangle"
        tone="teal"
        :trend="$trends['expired_permits'] ?? '+0%'"
    />
</section>
