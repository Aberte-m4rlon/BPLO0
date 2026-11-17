@extends('layouts.app')

@section('title', 'BPLO Personnel Portal')

@php use App\Models\Application; @endphp

@section('content')
    <div class="page-header bplo-page-header bplo-dashboard-hero">
        <div class="bplo-hero-copy">
            <span class="bplo-hero-pill">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M23 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                MUNICIPAL PERSONNEL WORKSPACE
            </span>
            <h1>Good day, <span class="bplo-hero-highlight">{{ auth()->user()->name }}</span>!</h1>
            <p>Manage application reviews and permit releases from one place.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('bplo.applications.index') }}" class="btn bplo-hero-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                </svg>
                Application Review
            </a>
            <a href="{{ route('bplo.permits.index') }}" class="btn bplo-hero-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 2 11 13 M22 2l-7 20-4-9-9-4 20-7z" />
                </svg>
                Permit Release
            </a>
        </div>
    </div>

    <div class="card bplo-flow-card">
        <div class="card-head">
            <div>
                <p class="bplo-eyebrow">AT A GLANCE</p>
                <h2 class="section">Permit Workflow</h2>
                <p class="muted mb-0">Follow each application from submission through permit issuance.</p>
            </div>
            <span class="bplo-live-label"><span aria-hidden="true"></span> Live workflow</span>
        </div>
        <div class="bplo-workflow">
            <a class="bplo-workflow-step" href="{{ route('bplo.applications.index', ['status' => Application::STATUS_SUBMITTED]) }}">
                <span class="step-number">01</span>
                <span class="workflow-step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M12 18v-6 M9 15h6" />
                    </svg>
                </span>
                <strong>Application submitted</strong>
                <span class="workflow-pill workflow-pill-blue">{{ $counts['pending_review'] }} new</span>
            </a>
            <a class="bplo-workflow-step" href="{{ route('bplo.applications.index') }}">
                <span class="step-number">02</span>
                <span class="workflow-step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                    </svg>
                </span>
                <strong>BPLO review</strong>
                <span class="workflow-pill workflow-pill-blue">{{ $counts['pending_review'] + $counts['in_review'] }} in queue</span>
            </a>
            <a class="bplo-workflow-step" href="{{ route('bplo.applications.index', ['status' => Application::STATUS_PAYMENT_PENDING]) }}">
                <span class="step-number">03</span>
                <span class="workflow-step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z M9 12l2 2 4-4" />
                    </svg>
                </span>
                <strong>Treasurer verifies payment</strong>
                <span class="workflow-pill workflow-pill-amber">{{ $counts['awaiting_payment'] }} awaiting verification</span>
            </a>
            <a class="bplo-workflow-step" href="{{ route('bplo.permits.index', ['status' => Application::STATUS_PAYMENT_VERIFIED]) }}">
                <span class="step-number">04</span>
                <span class="workflow-step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M9 15l2 2 4-4" />
                    </svg>
                </span>
                <strong>BPLO releases permit</strong>
                <span class="workflow-pill workflow-pill-indigo">{{ $counts['paid'] }} ready to release</span>
            </a>
            <a class="bplo-workflow-step" href="{{ route('bplo.permits.index', ['status' => Application::STATUS_PERMIT_RELEASED]) }}">
                <span class="step-number">05</span>
                <span class="workflow-step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9" /><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><rect x="6" y="14" width="12" height="8" />
                    </svg>
                </span>
                <strong>Permit issued</strong>
                <span class="workflow-pill workflow-pill-green">{{ $counts['released'] }} released</span>
            </a>
        </div>
    </div>

    <div class="bplo-middle-grid">
        <section class="card">
            <div class="card-head">
                <h2 class="section">Application Statistics</h2>
                <a href="{{ route('bplo.applications.index') }}" class="btn btn-sm btn-secondary">Details →</a>
            </div>
            <div class="bplo-mini-stats-grid">
                <div class="bplo-mini-stat">
                    <div class="bplo-mini-stat-top">
                        <span class="bplo-mini-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /></svg>
                        </span>
                    </div>
                    <div class="bplo-mini-stat-val">{{ $counts['total'] }}</div>
                    <div class="bplo-mini-stat-lbl">Submitted</div>
                </div>
                <div class="bplo-mini-stat">
                    <div class="bplo-mini-stat-top">
                        <span class="bplo-mini-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                        </span>
                    </div>
                    <div class="bplo-mini-stat-val">{{ $counts['pending_review'] }}</div>
                    <div class="bplo-mini-stat-lbl">In Review</div>
                </div>
                <div class="bplo-mini-stat">
                    <div class="bplo-mini-stat-top">
                        <span class="bplo-mini-stat-icon is-amber" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 0 8 4 4 0 0 0 0-8z" /></svg>
                        </span>
                    </div>
                    <div class="bplo-mini-stat-val">{{ $counts['in_review'] }}</div>
                    <div class="bplo-mini-stat-lbl">Under Review</div>
                </div>
                <div class="bplo-mini-stat">
                    <div class="bplo-mini-stat-top">
                        <span class="bplo-mini-stat-icon is-green" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5" /></svg>
                        </span>
                    </div>
                    <div class="bplo-mini-stat-val">{{ $counts['approved'] }}</div>
                    <div class="bplo-mini-stat-lbl">Approved</div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <h2 class="section">Recent Activities</h2>
                <a href="{{ route('bplo.applications.index') }}" class="btn btn-sm btn-secondary">View All →</a>
            </div>
            @if ($recentActivities->isEmpty())
                <x-empty-state title="No recent activity" message="Application reviews and releases will appear here." />
            @else
                <ul class="bplo-feed-list">
                    @foreach ($recentActivities as $log)
                        <li class="bplo-feed-item">
                            <span class="bplo-feed-left">
                                <span class="bplo-feed-dot {{ str_contains($log->action, 'release') || str_contains($log->action, 'verify') ? 'is-green' : '' }}"></span>
                                <span>{{ ucfirst(str_replace(['_', '.'], ' ', $log->action)) }}</span>
                            </span>
                            <span class="bplo-feed-time">{{ $log->created_at?->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="card">
            <div class="card-head">
                <h2 class="section">Notifications</h2>
                <a href="{{ route('bplo.applications.index') }}" class="btn btn-sm btn-secondary">View All →</a>
            </div>
            @if ($notifications->isEmpty())
                <x-empty-state title="All caught up" message="New application and payment alerts appear here." />
            @else
                <ul class="bplo-feed-list">
                    @foreach ($notifications as $notification)
                        <li class="bplo-feed-item">
                            <span class="bplo-feed-left">
                                <span class="bplo-feed-dot {{ $notification->read_at ? 'is-indigo' : 'is-amber' }}"></span>
                                <span>{{ $notification->title }}</span>
                            </span>
                            <span class="bplo-feed-time">{{ $notification->created_at?->diffForHumans(short: true) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <div class="stat-grid">
        <x-stat-card :value="$counts['for_correction']" label="For Correction" accent="amber" icon="correction" />
        <x-stat-card :value="$counts['awaiting_payment']" label="Payment Verification" accent="amber" icon="payment" />
        <x-stat-card :value="$counts['paid']" label="Ready to Release" accent="green" icon="release" />
        <x-stat-card :value="$counts['released']" label="Permits Released" accent="green" icon="permit" />
    </div>

    <div class="bplo-queue-grid">
        <section class="card">
            <div class="card-head">
                <h2 class="section">Application Review Queue</h2>
                <a href="{{ route('bplo.applications.index') }}" class="btn btn-sm btn-secondary">All applications</a>
            </div>
            @if ($reviewQueue->isEmpty())
                <x-empty-state icon="📋" title="Review queue is clear"
                    message="Newly submitted applications will appear here." />
            @else
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>Tracking No.</th><th>Business</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($reviewQueue as $application)
                                <tr>
                                    <td>{{ $application->tracking_number }}</td>
                                    <td>{{ $application->business_name }}</td>
                                    <td><x-status-badge :status="$application->status" /></td>
                                    <td><a href="{{ route('bplo.applications.show', $application) }}" class="btn btn-sm">Review</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="card">
            <div class="card-head">
                <h2 class="section">Ready for Permit Release</h2>
                <a href="{{ route('bplo.permits.index') }}" class="btn btn-sm btn-secondary">Release desk</a>
            </div>
            @if ($releaseQueue->isEmpty())
                <x-empty-state icon="🧾" title="No permits ready to release"
                    message="Applications appear here after the Treasurer verifies payment." />
            @else
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>Tracking No.</th><th>Business</th><th>Payment</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($releaseQueue as $application)
                                <tr>
                                    <td>{{ $application->tracking_number }}</td>
                                    <td>{{ $application->business_name }}</td>
                                    <td><x-status-badge :status="$application->status" /></td>
                                    <td><a href="{{ route('bplo.applications.show', $application) }}" class="btn btn-sm btn-success">Open to release</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <section class="card">
        <div class="card-head">
            <h2 class="section">Recent Applications</h2>
            <div class="bplo-header-actions">
                <form method="GET" action="{{ route('bplo.applications.index') }}" class="bplo-inline-search">
                    <input type="text" name="q" placeholder="Search applications..." aria-label="Search applications" style="min-height:36px;padding:6px 12px;font-size:0.8rem;">
                    <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
                </form>
                <a href="{{ route('bplo.applications.index') }}" class="btn btn-sm btn-secondary">View All →</a>
            </div>
        </div>
        @if ($recent->isEmpty())
            <x-empty-state icon="📋" title="No applications yet"
                message="Submitted applications will appear here and enter the review workflow." />
        @else
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Application No.</th><th>Business Name</th><th>Applicant</th><th>Date Submitted</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach ($recent as $application)
                            <tr>
                                <td>{{ $application->tracking_number }}</td>
                                <td>{{ $application->business_name }}</td>
                                <td>{{ $application->applicant_name }}</td>
                                <td>{{ $application->submitted_at?->format('M d, Y') }}</td>
                                <td><x-status-badge :status="$application->status" /></td>
                                <td><a href="{{ route('bplo.applications.show', $application) }}" class="btn btn-sm">Review</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
