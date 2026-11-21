@extends('layouts.app')

@section('title', 'Released Permits')

@section('content')
    <div class="page-header bplo-page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Inspection &amp; Monitoring</p>
            <h1>Permit Monitoring</h1>
            <p>Search permits issued by BPLO, inspect records, and review current compliance validity.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ url('/inspector/dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <div class="card">
        <div class="bplo-card-intro">
            <div>
                <h2 class="section">Released Permits Directory</h2>
                <p class="muted mb-0">Filter issued business permits by tracking details or compliance condition.</p>
            </div>
            <span class="bplo-result-count">{{ $permits->total() }} {{ \Illuminate\Support\Str::plural('permit', $permits->total()) }}</span>
        </div>

        <form method="GET" action="{{ url('/inspector/permits') }}" class="toolbar">
            <div class="form-group">
                <label for="q">Search</label>
                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Permit no. / business / address">
            </div>
            <div class="form-group">
                <label for="condition">Permit Condition</label>
                <select id="condition" name="condition">
                    <option value="">All permits</option>
                    <option value="VALID" @selected($condition === 'VALID')>Active</option>
                    <option value="EXPIRED" @selected($condition === 'EXPIRED')>Expired</option>
                    <option value="REVOKED" @selected($condition === 'REVOKED')>Revoked</option>
                </select>
            </div>
            <div class="form-group bplo-filter-actions">
                <button type="submit" class="btn">Apply</button>
                <a href="{{ url('/inspector/permits') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Permit No.</th><th>Business</th><th>Address</th><th>Record Status</th><th>Valid Until</th><th>Compliance</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse ($permits as $permit)
                        @php
                            $isExpired = $permit->valid_until !== null && $permit->valid_until->isPast();
                            $isRevoked = $permit->status === \App\Models\Permit::STATUS_REVOKED;
                            if ($isRevoked) {
                                $indicator = ['Revoked', 'badge-red'];
                            } elseif ($isExpired) {
                                $indicator = ['Expired', 'badge-yellow'];
                            } else {
                                $indicator = ['Active', 'badge-green'];
                            }
                        @endphp
                        <tr>
                            <td>{{ $permit->permit_number }}</td>
                            <td>{{ $permit->business_name }}</td>
                            <td>{{ $permit->business_address }}</td>
                            <td><span class="badge badge-blue">{{ ucfirst(strtolower($permit->status)) }}</span></td>
                            <td>{{ $permit->valid_until?->format('M d, Y') ?? '—' }}</td>
                            <td><span class="badge {{ $indicator[1] }}">{{ $indicator[0] }}</span></td>
                            <td><a href="{{ route('inspector.permits.show', $permit) }}" class="btn btn-sm">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="muted">No released permits match your search.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$permits" />
    </div>
@endsection
