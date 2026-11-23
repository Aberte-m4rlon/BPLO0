<x-treasurer.layout title="Treasurer Dashboard">
    <x-print-header
        office="Office of the Municipal Treasurer"
        title="Treasury Dashboard Executive Summary"
    />

    <x-admin.welcome-banner
        :user-name="auth()->user()->name"
        kicker="TREASURY & REVENUE WORKSPACE"
        subtitle="Verify business permit fee payments, monitor Bongabong collection totals, configure fee schedules, and inspect revenue analytics."
        primary-href="/treasurer/payments"
        primary-label="Verify Payments"
        secondary-href="/treasurer/analytics"
        secondary-label="Full Analytics"
        watermark-title="Bongabong"
        watermark-sub="Progress Together"
    />

    <x-treasurer.dashboard-stats :totals="$totals" />

    <x-treasurer.analytics-overview :totals="$totals" :analytics="$analytics" />

    <x-treasurer.revenue-breakdowns :analytics="$analytics" />

    <div class="adm-main-grid">
        <section class="card adm-panel" aria-label="Payments Awaiting Verification">
            <div class="card-head adm-panel-head">
                <div class="adm-panel-title-wrap">
                    <span class="adm-panel-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9" /><path d="M12 6v6l4 2" />
                        </svg>
                    </span>
                    <h2 class="adm-panel-title">Payments Awaiting Verification</h2>
                </div>
                <a href="{{ route('treasurer.payments.index') }}" class="btn btn-sm adm-pill-btn no-print">All Payments →</a>
            </div>

            @if ($pending->isEmpty())
                <x-empty-state icon="💸" title="No payments waiting"
                    message="Submitted applicant receipts will show up here for verification." />
            @else
                <div class="table-wrap adm-activity-table-wrap">
                    <table class="data adm-activity-table">
                        <thead>
                            <tr>
                                <th>TRACKING NO.</th>
                                <th>BUSINESS</th>
                                <th>METHOD</th>
                                <th class="text-right">AMOUNT</th>
                                <th class="no-print">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pending as $payment)
                                <tr>
                                    <td class="adm-activity-when">{{ $payment->tracking_number }}</td>
                                    <td>{{ $payment->application?->business_name ?? '—' }}</td>
                                    <td><span class="badge adm-action-badge">{{ \App\Support\PaymentMethods::label($payment->method) }}</span></td>
                                    <td class="text-right"><strong>&#8369;{{ number_format((float) $payment->amount, 2) }}</strong></td>
                                    <td class="no-print"><a href="{{ route('treasurer.payments.show', $payment) }}" class="btn btn-sm">Verify</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="adm-activity-mobile-list" aria-label="Payments awaiting verification cards">
                    @foreach ($pending as $payment)
                        <article class="adm-activity-mobile-card">
                            <div class="adm-activity-mobile-top">
                                <strong class="adm-actor-name">{{ $payment->application?->business_name ?? $payment->tracking_number }}</strong>
                                <span class="badge adm-action-badge">{{ \App\Support\PaymentMethods::label($payment->method) }}</span>
                            </div>
                            <div class="adm-activity-mobile-meta">
                                <span class="adm-activity-mobile-subject">{{ $payment->tracking_number }} · <strong>&#8369;{{ number_format((float) $payment->amount, 2) }}</strong></span>
                                <a href="{{ route('treasurer.payments.show', $payment) }}" class="btn btn-sm">Verify</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="card adm-panel adm-quick-panel bplo-sticky-card no-print" aria-label="Treasury Management">
            <div class="card-head adm-panel-head">
                <div class="adm-panel-title-wrap">
                    <span class="adm-panel-icon adm-panel-icon-zap" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                        </svg>
                    </span>
                    <h2 class="adm-panel-title">Treasury Controls</h2>
                </div>
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                    <button type="button" onclick="window.print()" class="btn btn-sm adm-pill-btn">Print Summary</button>
                    <a href="{{ route('treasurer.payments.export') }}" class="btn btn-sm adm-pill-btn">Export CSV ↓</a>
                </div>
            </div>

            <div class="adm-quick-grid">
                <x-admin.quick-action-card
                    href="/treasurer/payments"
                    title="Payments Queue"
                    description="Verify receipts & collections"
                    icon="wallet"
                />
                <x-admin.quick-action-card
                    href="/treasurer/analytics"
                    title="Revenue Analytics"
                    description="Totals, trends & barangays"
                    icon="bar-chart"
                />
                <x-admin.quick-action-card
                    href="/treasurer/fee-types"
                    title="Fee Types"
                    description="Municipal fee categories"
                    icon="tag"
                />
                <x-admin.quick-action-card
                    href="/treasurer/fee-rates"
                    title="Fee Rates"
                    description="Active peso fee schedules"
                    icon="calculator"
                />
            </div>
        </section>
    </div>

    <x-print-footer left-role="Treasury Staff" right-role="Office of the Municipal Treasurer" />
</x-treasurer.layout>
