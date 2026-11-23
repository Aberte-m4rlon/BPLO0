<footer class="footer adm-footer no-print">
    <div class="adm-footer-left">
        <span class="adm-footer-shield" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z M9 12l2 2 4-4" />
            </svg>
        </span>
        <span class="adm-footer-brand">{{ trim(str_ireplace('Municipality of ', '', config('permit.municipality', 'Bongabong'))) }} Business Permits Office</span>
        <span class="adm-footer-tagline">• Secure • Reliable • Efficient</span>
    </div>
    <div class="adm-footer-right">
        <span class="adm-footer-clock">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
            </svg>
            <time id="adm-live-clock" datetime="{{ now()->toIso8601String() }}">{{ now()->format('M d, Y g:i A') }}</time>
        </span>
        <span class="adm-footer-online">
            <span class="adm-online-dot" aria-hidden="true"></span>
            Online
        </span>
    </div>
</footer>
