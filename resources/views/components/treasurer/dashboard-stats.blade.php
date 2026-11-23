@props([
    'totals' => [],
])

<section class="adm-stats-grid trs-stats-grid" aria-label="Treasury financial totals">
    <x-admin.stat-card
        :value="'₱'.number_format((float) ($totals['total_collected'] ?? 0), 2)"
        label="Total Collected"
        icon="wallet"
        tone="green"
        :trend="$totals['month_trend'] ?? '+0%'"
    />
    <x-admin.stat-card
        :value="'₱'.number_format((float) ($totals['collected_this_month'] ?? 0), 2)"
        label="Collected This Month"
        icon="trending-up"
        tone="blue"
        :trend="($totals['this_month_count'] ?? 0).' paid'"
    />
    <x-admin.stat-card
        :value="'₱'.number_format((float) ($totals['awaiting_amount'] ?? 0), 2)"
        label="Awaiting Verification"
        icon="clock"
        tone="amber"
        :trend="($totals['awaiting_count'] ?? 0).' queued'"
    />
    <x-admin.stat-card
        :value="'₱'.number_format((float) ($totals['pending_amount'] ?? 0), 2)"
        label="Pending Receivables"
        icon="file-text"
        tone="purple"
        :trend="($totals['pending_count'] ?? 0).' unpaid'"
    />
    <x-admin.stat-card
        :value="(string) ($totals['verified_count'] ?? 0)"
        label="Verified Payments"
        icon="check-circle"
        tone="teal"
        :trend="($totals['collection_rate'] ?? 0).'% rate'"
    />
</section>
