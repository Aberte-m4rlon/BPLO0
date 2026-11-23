<x-treasurer.layout title="Revenue Analytics">
    <x-print-header
        office="Office of the Municipal Treasurer"
        title="Official Collections & Revenue Analytics Report"
        :subtitle="'Reporting Period: '.($period === 'all' ? 'All Time' : ($period === 'year' ? 'This Year ('.now()->year.')' : now()->format('F Y')))"
    />

    <div class="page-header adm-panel trs-report-header no-print">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">{{ config('permit.municipality') }} · Treasury</p>
            <h1>Collections &amp; Revenue Analytics</h1>
            <p>Financial totals, monthly trends, fee category shares, and barangay collections.</p>
        </div>
        <div class="bplo-header-actions trs-filter-actions">
            <div class="trs-period-pills" role="group" aria-label="Reporting period">
                <a href="{{ route('treasurer.analytics', ['period' => 'all']) }}" class="btn btn-sm {{ $period === 'all' ? '' : 'btn-secondary' }}">All Time</a>
                <a href="{{ route('treasurer.analytics', ['period' => 'year']) }}" class="btn btn-sm {{ $period === 'year' ? '' : 'btn-secondary' }}">This Year</a>
                <a href="{{ route('treasurer.analytics', ['period' => 'month']) }}" class="btn btn-sm {{ $period === 'month' ? '' : 'btn-secondary' }}">This Month</a>
            </div>
            <a href="{{ route('treasurer.payments.export') }}" class="btn btn-sm adm-pill-btn">Export CSV ↓</a>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-secondary no-print">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print Report
            </button>
        </div>
    </div>

    <x-treasurer.dashboard-stats :totals="$totals" />

    <x-treasurer.analytics-overview :totals="$totals" :analytics="$analytics" />

    <x-treasurer.revenue-breakdowns :analytics="$analytics" />

    <section class="card adm-panel" aria-label="Recent Payment Ledger">
        <div class="card-head adm-panel-head">
            <div class="adm-panel-title-wrap">
                <span class="adm-panel-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M16 13H8 M16 17H8" />
                    </svg>
                </span>
                <h2 class="adm-panel-title">Recent Financial Transactions</h2>
            </div>
            <a href="{{ route('treasurer.payments.index') }}" class="btn btn-sm adm-pill-btn no-print">All Payments →</a>
        </div>

        @if ($recentTransactions->isEmpty())
            <x-empty-state icon="📊" title="No payment transactions yet"
                message="Transactions for the selected period will appear here." />
        @else
            <div class="table-wrap adm-activity-table-wrap">
                <table class="data adm-activity-table">
                    <thead>
                        <tr>
                            <th>TRACKING NO.</th>
                            <th>BUSINESS</th>
                            <th>BARANGAY</th>
                            <th>METHOD</th>
                            <th>STATUS</th>
                            <th class="text-right">AMOUNT</th>
                            <th>DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTransactions as $tx)
                            <tr>
                                <td class="adm-activity-when">{{ $tx->tracking_number }}</td>
                                <td>{{ $tx->application?->business_name ?? '—' }}</td>
                                <td>{{ $tx->application?->barangay ?? '—' }}</td>
                                <td>{{ \App\Support\PaymentMethods::label($tx->method) }}</td>
                                <td><span class="badge adm-action-badge">{{ strtoupper($tx->status) }}</span></td>
                                <td class="text-right"><strong>&#8369;{{ number_format((float) $tx->amount, 2) }}</strong></td>
                                <td>{{ ($tx->verified_at ?? $tx->created_at)?->format('M d, Y g:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="adm-activity-mobile-list" aria-label="Recent financial transactions cards">
                @foreach ($recentTransactions as $tx)
                    <article class="adm-activity-mobile-card">
                        <div class="adm-activity-mobile-top">
                            <strong class="adm-actor-name">{{ $tx->application?->business_name ?? $tx->tracking_number }}</strong>
                            <span class="badge adm-action-badge">{{ strtoupper($tx->status) }}</span>
                        </div>
                        <div class="adm-activity-mobile-meta">
                            <span>{{ $tx->tracking_number }} · {{ \App\Support\PaymentMethods::label($tx->method) }}</span>
                            <strong>&#8369;{{ number_format((float) $tx->amount, 2) }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <x-print-footer left-role="Treasury Staff / Collector" right-role="Office of the Municipal Treasurer" />
</x-treasurer.layout>
