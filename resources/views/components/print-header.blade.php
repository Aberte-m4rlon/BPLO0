@props([
    'office' => 'Business Permits and Licensing Office',
    'title' => 'Official Municipal Report',
    'subtitle' => null,
    'metaRef' => null,
])

<header class="print-only-header" aria-hidden="true">
    <div class="print-header-seal">BP</div>
    <div class="print-header-center">
        <p class="print-header-republic">Republic of the Philippines · Province of {{ config('permit.province') }}</p>
        <h2>{{ strtoupper(config('permit.municipality')) }}</h2>
        <p class="print-header-office">{{ $office }}</p>
        <h3>{{ $title }}</h3>
        @if ($subtitle)
            <p class="print-header-sub">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="print-header-meta">
        <div><span>Generated</span><strong>{{ now()->format('M d, Y g:i A') }}</strong></div>
        <div><span>Prepared By</span><strong>{{ auth()->user()?->name ?? 'System' }}</strong></div>
        @if ($metaRef)
            <div><span>Reference</span><strong>{{ $metaRef }}</strong></div>
        @endif
    </div>
</header>
