<section class="card adm-panel adm-quick-panel bplo-sticky-card" aria-label="Quick Administration">
    <div class="card-head adm-panel-head">
        <div class="adm-panel-title-wrap">
            <span class="adm-panel-icon adm-panel-icon-zap" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                </svg>
            </span>
            <h2 class="adm-panel-title">Quick Administration</h2>
        </div>
    </div>

    <div class="adm-quick-grid">
        <x-admin.quick-action-card
            href="/admin/users"
            title="Users & Roles"
            description="Accounts, roles & access"
            icon="users"
        />
        <x-admin.quick-action-card
            href="/admin/requirement-types"
            title="Requirements"
            description="Permit upload checklist"
            icon="file-text"
        />
        <x-admin.quick-action-card
            href="/admin/audit-logs"
            title="Audit Logs"
            description="System activity records"
            icon="activity"
        />
        <x-admin.quick-action-card
            href="/admin/settings"
            title="Settings"
            description="System configuration"
            icon="settings"
        />
    </div>
</section>
