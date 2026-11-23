@props([
    'totals' => [],
    'analytics' => [],
])

@php
    $monthly = $analytics['monthly_series'] ?? [];
    $methods = $analytics['method_breakdown'] ?? [];
    $pipeline = $analytics['status_pipeline'] ?? [];
@endphp

<div class="adm-main-grid trs-analytics-grid">
    <section class="card adm-panel" aria-label="Monthly Revenue Trend">
        <div class="card-head adm-panel-head">
            <div class="adm-panel-title-wrap">
                <span class="adm-panel-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18 M18 17V9 M13 17V5 M8 17v-3" />
                    </svg>
                </span>
                <h2 class="adm-panel-title">Monthly Collection Trend (6 Months)</h2>
            </div>
            <a href="{{ route('treasurer.analytics') }}" class="btn btn-sm adm-pill-btn no-print">Full Report →</a>
        </div>

        <div class="trs-summary-strip">
            <div class="trs-mini-metric">
                <span>Average Fee</span>
                <strong>&#8369;{{ number_format((float) ($totals['average_payment'] ?? 0), 2) }}</strong>
            </div>
            <div class="trs-mini-metric">
                <span>Verified Today</span>
                <strong>&#8369;{{ number_format((float) ($totals['verified_today_amount'] ?? 0), 2) }} <small>({{ $totals['verified_today_count'] ?? 0 }})</small></strong>
            </div>
            <div class="trs-mini-metric">
                <span>Failed / Rejected</span>
                <strong>&#8369;{{ number_format((float) ($totals['failed_amount'] ?? 0), 2) }} <small>({{ $totals['failed_count'] ?? 0 }})</small></strong>
            </div>
        </div>

        <div class="trs-bar-chart" role="list" aria-label="Monthly collections bar chart">
            @foreach ($monthly as $bar)
                <div class="trs-bar-col" role="listitem" title="{{ $bar['label'] }}: ₱{{ number_format((float) $bar['amount'], 2) }} ({{ $bar['count'] }} payments)">
                    <span class="trs-bar-val">&#8369;{{ $bar['amount'] >= 1000 ? number_format($bar['amount'] / 1000, 1).'k' : number_format($bar['amount'], 0) }}</span>
                    <div class="trs-bar-track">
                        <div class="trs-bar-fill {{ $bar['amount'] > 0 ? 'has-value' : '' }}" style="height: {{ $bar['bar_pct'] }}%"></div>
                    </div>
                    <span class="trs-bar-label">{{ $bar['short'] }}</span>
                    <span class="trs-bar-count">{{ $bar['count'] }} tx</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="card adm-panel" aria-label="Payment Methods and Collection Pipeline">
        <div class="card-head adm-panel-head">
            <div class="adm-panel-title-wrap">
                <span class="adm-panel-icon adm-panel-icon-zap" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M12 3v9l6 3" />
                    </svg>
                </span>
                <h2 class="adm-panel-title">Collection Channels &amp; Status</h2>
            </div>
        </div>

        <div class="trs-channel-list">
            @forelse ($methods as $method)
                <div class="trs-progress-item">
                    <div class="trs-progress-head">
                        <strong>{{ $method['label'] }} <small>({{ $method['count'] }} tx)</small></strong>
                        <span>&#8369;{{ number_format((float) $method['amount'], 2) }} · {{ $method['share_pct'] }}%</span>
                    </div>
                    <div class="trs-progress-track">
                        <div class="trs-progress-fill" style="width: {{ max(4, min(100, (float) $method['share_pct'])) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="muted mb-0">No verified payments recorded yet.</p>
            @endforelse
        </div>

        <div class="trs-pipeline-grid" aria-label="Payment pipeline summary">
            @foreach ($pipeline as $item)
                <div class="trs-pipeline-tile tone-{{ $item['tone'] }}">
                    <small>{{ $item['label'] }}</small>
                    <strong>&#8369;{{ number_format((float) $item['amount'], 2) }}</strong>
                    <span>{{ $item['count'] }} {{ \Illuminate\Support\Str::plural('record', $item['count']) }}</span>
                </div>
            @endforeach
        </div>
    </section>
</div>
