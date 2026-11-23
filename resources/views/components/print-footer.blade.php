@props([
    'leftTitle' => 'Prepared & Verified By',
    'leftRole' => null,
    'rightTitle' => 'Noted By',
    'rightRole' => 'Municipal Treasurer',
])

<footer class="print-only-footer" aria-hidden="true">
    <div class="print-signatures">
        <div class="print-sig-col">
            <div class="print-sig-line"></div>
            <strong>{{ auth()->user()?->name ?? $leftTitle }}</strong>
            <span>{{ $leftRole ?? $leftTitle }}</span>
        </div>
        <div class="print-sig-col">
            <div class="print-sig-line"></div>
            <strong>{{ $rightTitle }}</strong>
            <span>{{ $rightRole }} · {{ config('permit.municipality') }}</span>
        </div>
    </div>
    <p class="print-footer-note">
        Official system-generated document — {{ config('permit.municipality') }}, {{ config('permit.province') }}
    </p>
</footer>
