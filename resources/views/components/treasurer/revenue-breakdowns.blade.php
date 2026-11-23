@props([
    'analytics' => [],
])

@php
    $categories = $analytics['fee_category_breakdown'] ?? [];
    $barangays = $analytics['barangay_collections'] ?? [];
@endphp

<div class="adm-main-grid trs-breakdowns-grid">
    <section class="card adm-panel" aria-label="Revenue by Fee Category">
        <div class="card-head adm-panel-head">
            <div class="adm-panel-title-wrap">
                <span class="adm-panel-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z M7 7h.01" />
                    </svg>
                </span>
                <h2 class="adm-panel-title">Revenue by Fee Category</h2>
            </div>
            <a href="{{ route('treasurer.fee-rates.index') }}" class="btn btn-sm adm-pill-btn">Fee Schedules →</a>
        </div>

        @if (empty($categories))
            <x-empty-state icon="🏷️" title="No fee breakdown yet"
                message="Fee category totals will populate as permit payments are verified." />
        @else
            <div class="trs-channel-list">
                @foreach ($categories as $cat)
                    <div class="trs-progress-item">
                        <div class="trs-progress-head">
                            <strong>{{ $cat['name'] }} <small>({{ $cat['tx_count'] }} applied)</small></strong>
                            <span>&#8369;{{ number_format((float) $cat['amount'], 2) }} · {{ $cat['share_pct'] }}%</span>
                        </div>
                        <div class="trs-progress-track">
                            <div class="trs-progress-fill trs-fill-teal" style="width: {{ max(4, min(100, (float) $cat['share_pct'])) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="card adm-panel" aria-label="Top Barangays by Collection">
        <div class="card-head adm-panel-head">
            <div class="adm-panel-title-wrap">
                <span class="adm-panel-icon adm-panel-icon-zap" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" /><circle cx="12" cy="10" r="3" />
                    </svg>
                </span>
                <h2 class="adm-panel-title">Collections by Barangay (Bongabong)</h2>
            </div>
        </div>

        @if (empty($barangays))
            <x-empty-state icon="📍" title="No barangay collections yet"
                message="Verified collections grouped by business barangay will display here." />
        @else
            <div class="trs-channel-list">
                @foreach ($barangays as $item)
                    <div class="trs-progress-item">
                        <div class="trs-progress-head">
                            <strong>{{ $item['barangay'] }} <small>({{ $item['tx_count'] }} {{ \Illuminate\Support\Str::plural('permit', $item['tx_count']) }})</small></strong>
                            <span>&#8369;{{ number_format((float) $item['amount'], 2) }} · {{ $item['share_pct'] }}%</span>
                        </div>
                        <div class="trs-progress-track">
                            <div class="trs-progress-fill" style="width: {{ max(4, min(100, (float) $item['share_pct'])) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
